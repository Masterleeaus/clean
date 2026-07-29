# Chat-first job completion, invoicing and collection

## Outcome

An authorised company user can tell Titan Zero:

```text
I completed job TZ-1042. Send the invoice.
```

Titan Zero then executes this governed sequence:

1. Resolve the matching WorkCore work order inside the mapped WorkCore company.
2. Reject ambiguous matches and ask for the exact job number.
3. Pass the user statement into WorkCore as explicit confirmation.
4. Complete the work order through `workcore.work_order.change_status`.
5. Allow WorkCore compliance gates, permissions, audit and idempotency to run.
6. Project the WorkCore customer and service address into Titan Money.
7. create an idempotent `completed_job` billable event.
8. Run the Invoice Agent for that exact event.
9. Auto-issue within the company’s configured authority, or create an approval request.
10. Create or reuse one secure Titan Pay collection session.
11. Generate a QR code whose payload is the secure invoice collection page.
12. Return the invoice, amount, payment link and QR inside the originating Titan Zero chat.
13. Queue delivery to consented customer channels.
14. Continue due and overdue follow-up until payment, dispute, hold, void or write-off stops collection.

Titan Zero never updates WorkCore tables directly. Browser return pages and QR scans never confirm payment.

## Required WorkCore placement

The compatibility bridge supports either runtime placement:

```text
App\Domains\WorkCore\System\...
App\Extensions\WorkCore\System\...
```

WorkCore must provide its business-action runtime and these tables:

```text
tz_work_orders
tz_work_order_services
tz_customers
tz_premises (optional for service-address context)
```

In **Titan Money → Agents**, set the current Titan company’s numeric WorkCore company ID. This explicit mapping is required because Titan company IDs are ULIDs while the supplied WorkCore package uses numeric company IDs.

In the same form, state whether WorkCore service prices already include GST. Titan Money uses that explicit setting when calculating an automatically generated invoice; it does not guess the tax basis of a WorkCore service price.

## Authority and confirmation

The chat user requires:

```text
workcore.work_order.complete
```

WorkCore independently evaluates its configured action permission and entitlement. Owners and company administrators pass Titan company permission checks; other workers need an explicit grant.

The completion wording itself is the explicit WorkCore confirmation. Titan Zero records a correlation ID linking the conversation, WorkCore action, billable event, invoice, collection session and agent run.

## Automatic invoice rules

New companies receive these starting policies:

- Invoice Agent enabled in bounded mode.
- Automatic issue enabled for approved source types.
- Initial maximum automatic issue authority: **A$1,000.00**.
- Payment terms: 14 days.
- Larger invoices or disallowed source types create approval requests.
- Receivables Agent enabled in bounded mode.
- Payment Reconciliation Agent remains observe-only and disabled until configured.

Change these values under **Titan Money → Agents**. WorkCore completion can still succeed when invoice approval is required; Titan Zero reports the actual invoice state rather than claiming it was sent.

## Partial outcomes

The workflow is intentionally not presented as an all-or-nothing database transaction across WorkCore, Titan Money, Titan Pay and external channels. Titan Zero reports the durable state that actually occurred:

- If WorkCore completed the job but invoice automation failed, the reply says the job is complete and gives a correlation reference for the invoice exception.
- If the invoice was issued but QR, collection or delivery preparation failed, the reply says the invoice remains valid and that payment-link or delivery work requires attention.
- A downstream failure never causes Titan Zero to claim that the completed job was rolled back.
- No partial outcome confirms a payment.

## Payment order

The customer page shows only configured/available methods, in this product order:

1. PayID
2. Bank transfer
3. Cash
4. Credit or debit card through PayPal

Stripe remains optional and is excluded unless `TITANPAY_ALLOW_CUSTOMER_STRIPE=true`.

### Protected payment configuration

Create active Titan Pay gateway connections using these protected references:

```text
PayID:        env:TITANPAY_PAYID
Bank:         env:TITANPAY_BANK
PayPal:       env:TITANPAY_PAYPAL
Cash:         no secret reference required
```

Environment values:

```dotenv
TITANPAY_PAYID_VALUE=
TITANPAY_PAYID_ACCOUNT_NAME=
TITANPAY_BANK_BSB=
TITANPAY_BANK_ACCOUNT_NUMBER=
TITANPAY_BANK_ACCOUNT_NAME=
TITANPAY_PAYPAL_CLIENT_ID=
TITANPAY_PAYPAL_CLIENT_SECRET=
TITANPAY_PAYPAL_WEBHOOK_ID=
```

A production Titan Vault resolver may replace the environment fallback without changing the gateway contracts.

PayID and bank transfer are not offered unless their protected details resolve successfully. Cash remains available. PayPal is offered only when its client and webhook credentials are available.

PayPal’s ability to show guest card checkout is controlled by the merchant’s PayPal eligibility and account configuration. Titan Pay labels the method as card through PayPal but does not falsely guarantee guest checkout.

## QR behaviour

Titan Pay generates a private, non-cached SVG QR at:

```text
/pay/{token}/qr.svg
```

The QR opens the Titan Pay collection page, not PayPal directly. This keeps PayID as the preferred method while allowing the customer to choose another available method.

QR rendering uses the bundled Python renderer under `resources/python/titanpay_qr` and requires Python 3. The bundled QR library licence is included beside its source.

## Customer delivery routes

For an issued invoice, Titan Money resolves customer consent and availability in this order:

1. Titan Channels customer app
2. SMS
3. Email
4. Automated voice call for configured follow-up stages

All available requested routes may be queued so a customer can receive both an app message and a fallback message. SMS, WhatsApp and voice obey company-local quiet hours. Voice is disabled by default for WorkCore-projected customers and the final 30-day voice stage requires human approval.

Customer preferences can be edited under **Titan Money → Customers**. A Channels app delivery additionally requires `customer_app_user_id` in customer metadata and an active company membership for that user.

When no consented or configured customer route exists, Titan Money writes an internal delivery exception into the company’s **Titan Money Alerts** channel. It does not mark an undelivered customer message as delivered.

## SMS and voice providers

Configure HTTPS provider webhooks:

```dotenv
TITAN_CHANNELS_SMS_WEBHOOK_URL=
TITAN_CHANNELS_SMS_WEBHOOK_TOKEN=
TITAN_CHANNELS_VOICE_WEBHOOK_URL=
TITAN_CHANNELS_VOICE_WEBHOOK_TOKEN=
TITAN_CHANNELS_WHATSAPP_WEBHOOK_URL=
TITAN_CHANNELS_WHATSAPP_WEBHOOK_TOKEN=
```

Each provider request contains:

- delivery id and idempotency key
- destination
- body and optional subject
- payment URL
- QR URL
- signed-receipt callback URL
- company and invoice metadata

Provider receipt callbacks must sign:

```text
{unix_timestamp}.{raw_json_body}
```

using HMAC-SHA256 and `TITAN_CHANNELS_RECEIPT_SECRET`, then send:

```text
X-Titan-Channels-Timestamp: <unix timestamp>
X-Titan-Channels-Signature: sha256=<hex signature>
```

Callback endpoint:

```text
POST /api/titan-money/v1/channel-receipts
```

Receipt updates are idempotent and monotonic: late `sent` or failure callbacks cannot downgrade an already delivered message.

## Follow-up sequence

Default stages:

| Stage | Customer routes | Governance |
|---|---|---|
| Due today | Channels app, email | Automatic |
| 3 days overdue | Channels app, SMS, email | Automatic |
| 7 days overdue | Channels app, SMS, email | Automatic |
| 14 days overdue | Customer routes plus internal alert | Automatic escalation |
| 30 days overdue | Customer routes, internal alert, optional voice | Human approval required |

Stages move forward only. Paid, voided, written-off, disputed and collection-hold invoices do not continue through the normal collection query.

## Runtime processes

Production needs:

```bash
php artisan schedule:work
php artisan queue:work --tries=5 --timeout=120
```

Scheduled commands include:

```text
titan-money:agents-run
titan-money:generate-recurring
titan-money:queue-reminders
titan-money:publish-outbox
titan-money:dispatch-deliveries
```

## Safety invariants

- Repeating the chat command does not create duplicate invoices for the same WorkCore source version.
- Invoice creation locks the source invoice before creating or superseding a collection link.
- Only one recoverable active payment link is retained for the same balance and method set.
- QR scans never mark an invoice paid.
- Manual claims remain pending until authorised verification.
- PayPal capture does not itself update Titan Money payment truth; verified provider evidence remains authoritative.
- Payment amount, currency, merchant connection and idempotency are validated.
- Channel receipts require a timestamped HMAC signature.
- Missing channels create an internal exception rather than a false delivery result.
