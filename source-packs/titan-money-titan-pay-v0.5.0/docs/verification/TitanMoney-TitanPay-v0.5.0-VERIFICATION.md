# Titan Zero Meetup + Titan Money + Titan Pay v0.5.0 Verification

## Scope

This release adds the chat-first job-to-invoice-to-payment workflow requested for Titan Zero:

1. An authorised user states that a WorkCore job is complete in Titan Zero chat.
2. Titan Zero resolves the real WorkCore work order and completes it through `workcore.work_order.change_status`.
3. Titan Money creates an idempotent billable event and runs the Invoice Agent for that event.
4. The Invoice Agent issues automatically inside the company policy limit or creates an approval request.
5. Titan Pay creates or reuses one secure invoice collection link and private QR code.
6. The customer receives the invoice through Titan Channels, SMS, email and optional voice according to consent and configuration.
7. The Receivables Agent follows due and overdue invoices through forward-only governed stages.

## Functional verification

- Natural-language completion statements recognised, including job-first and contracted wording.
- Negated statements, questions and future intentions do not execute WorkCore actions.
- Ambiguous job matches request an exact job number.
- WorkCore writes occur only through its governed business-action dispatcher.
- WorkCore service-price GST basis is explicitly configurable.
- Invoice generation is idempotent by WorkCore source version.
- Chat correlation and conversation context survive Invoice Agent execution.
- Partial outcomes report the durable job/invoice state truthfully.
- Customer payment order is PayID, bank transfer, cash, then card through PayPal when configured.
- PayID and bank transfer are hidden unless protected payment details resolve.
- QR code opens the secure Titan Pay invoice page; scanning does not confirm payment.
- Email and customer-app messages contain the payment link and QR.
- SMS/WhatsApp webhooks receive the payment URL; voice webhooks receive the invoice/payment payload.
- Delivery receipts require timestamped HMAC-SHA256 signatures.
- Missing customer routes or QR/payment-link preparation produce internal staff exceptions.
- Paid, voided, written-off, disputed and collection-hold invoices stop normal receivables progression.

## Static and regression evidence

- PHP syntax: **292 files passed**.
- Standalone regression/structural suites: **13 of 13 passed**.
- JavaScript parsing: passed for `public/js/chat-app.js` and `public/js/chat.js`.
- Bundled Python QR renderer: compiled and produced SVG output.
- JSON parsing: passed.
- Composer content hash: `composer.json` matches `composer.lock` (`3666c170aa7b07f81da798f8f022da46`).
- Duplicate local class declarations: **0** across 172 declarations.
- Retired Finance/ZeroPay runtime identifiers: **0**.
- Direct WorkCore table mutations from Titan Zero: **0**.
- Paid-status mutation review: the payment allocation service remains the only authority that sets `InvoiceStatus::Paid`.

## Source delta before release packaging

- Current source files: **404**.
- Changed or new files from v0.4.0: **99**.
- New files from v0.4.0: **63**.
- Modified files from v0.4.0: **36**.
- Deleted files from v0.4.0: **0**.

## Runtime requirements

- Install the WorkCore business-action runtime and explicitly map each Titan company to its WorkCore company ID.
- Configure whether WorkCore service prices include GST.
- Configure active Titan Pay connections for PayID, bank transfer, cash and PayPal.
- Run both `php artisan schedule:work` and a queue worker.
- Configure mail and any SMS/WhatsApp/voice provider webhooks.
- Set `TITAN_CHANNELS_RECEIPT_SECRET` and require provider receipt signatures.
- Python 3 is required for the bundled QR renderer.

## Verification limits

The execution environment does not contain `vendor/`, Composer CLI, `node_modules`, a configured database or provider sandbox credentials. Therefore this report does not claim:

- a full Laravel boot or route-list run;
- migration execution against MySQL/PostgreSQL/SQLite;
- the Pest/Laravel integration suite;
- a production Vite build;
- live PayPal, SMS, voice, email or webhook delivery.

Those checks remain mandatory in a disposable deployment environment before production use.
