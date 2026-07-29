# Titan Pay Dispute Policy

## Overview

A dispute (also known as a chargeback or payment dispute) occurs when a payer challenges a transaction with their bank or payment provider. This policy outlines how disputes are handled within the Titan Pay module.

## Dispute Sources

Disputes may originate from:
- **Stripe chargebacks** — initiated by the payer's card issuer.
- **PayPal disputes** — opened via the PayPal Resolution Centre.
- **Bank-initiated reversals** — for bank transfer payments where the payer's bank reverses the deposit.
- **PayID dispute** — disputes raised via NPP dispute resolution process.

## Dispute Notification

When a dispute is received:
1. The gateway sends a webhook event to Titan Pay.
2. The event is recorded as a `Titan PayWebhookEvent` with type `dispute.created`.
3. The associated `Titan PaySession` status is updated to `disputed` (if applicable).
4. The operator receives a `Titan PayNotification` alerting them to the dispute.

## Operator Response

Operators must respond to disputes within the timeline set by the gateway:
- **Stripe**: 7–21 days from dispute notification (varies by card network).
- **PayPal**: 20 days from dispute opening.
- **Bank reversal**: Follow the bank's reversal dispute process (typically 5 business days).

Evidence that operators should provide:
- Session creation records
- Transaction records from `Titan PayTransaction`
- Gateway logs from `Titan PayGatewayLog`
- Any proof of service or delivery applicable to the business

## Dispute Outcomes

| Outcome | Description |
|---------|-------------|
| `won` | Dispute resolved in operator's favour; funds retained |
| `lost` | Dispute resolved in payer's favour; funds reversed; fee may apply |
| `withdrawn` | Payer withdrew the dispute |

## Chargeback Fees

Gateway chargeback fees are recorded as a separate `Titan PayTransaction` entry with a negative `net_amount` and type `chargeback_fee` in `meta`. These are NOT refundable.

## Prevention

To reduce dispute risk:
- Use session tokens as payment references so deposits can be matched unambiguously.
- Ensure payers receive notifications at `completed` status to confirm successful payment.
- Keep `Titan PayGatewayLog` records for at least 18 months as evidence.

## Sensitive Data in Disputes

Dispute evidence must never include:
- Raw card numbers or CVV
- Full bank account numbers
- Gateway API keys or secrets

Only provide masked or reference-level financial data in dispute evidence.
