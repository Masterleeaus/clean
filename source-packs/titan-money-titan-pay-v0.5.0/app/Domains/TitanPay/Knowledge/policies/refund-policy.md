# Titan Pay Refund Policy

## Scope

This policy applies to all payment sessions processed through the Titan Pay module, across all supported gateways.

## Refund Eligibility

A transaction is eligible for refund if:
1. The session status is `completed`.
2. The transaction has not already been refunded.
3. The refund request is made within the operator's configured refund window (default: 30 days from transaction completion).

Refunds are not available for:
- Sessions with status `pending`, `opened`, `processing`, `failed`, or `expired`.
- Transactions that have already been fully or partially refunded.
- Transactions beyond the refund window.

## Refund Methods

### Card Gateways (Stripe, PayPal)
Refunds are processed via the gateway adapter's `refundPayment()` method. Funds are returned to the original payment method. Processing time is 3–10 business days depending on the issuing bank.

### Bank Transfer
Refunds are processed manually by the operator. The operator initiates a return transfer to the original depositor account. Once completed, the operator marks the refund as complete in the admin panel. A refund `Titan PayTransaction` record is created with a negative amount.

### PayID
Refunds via PayID are processed as a new outbound transfer to the payer's PayID address. Processing time is typically same-day via NPP.

### Cash
Cash refunds are processed in-person by the operator. The operator records the refund in the admin panel with confirmation of cash returned.

### Cryptocurrency
Cryptocurrency refunds require the operator to manually return the agreed value to the payer's wallet address. Due to exchange rate volatility, refund value is based on the AUD (or fiat) amount at the time of original transaction, not crypto market value.

## Partial Refunds

Partial refunds are supported for Stripe and PayPal gateways. Partial amounts must not exceed the original transaction net amount minus any previously refunded amounts.

## Refund Audit Trail

All refund actions are recorded as `Titan PayTransaction` records with a negative `amount` and a `status` of `refunded`. The original `transaction_id` is referenced in the refund transaction's `meta` field for traceability.

## Operator Responsibilities

- Operators are responsible for validating refund eligibility before initiating.
- Refund approvals require the `titanpay.gateway.manage` or `titanpay.reconciliation.resolve` permission.
- All refunds are logged and visible to the account owner.

## Non-Refundable Situations

Titan Pay module does not process refunds for:
- Gateway fees charged by third-party providers (e.g. Stripe processing fees).
- Fraudulent transactions identified after the refund window closes.
- Sessions voided due to operator error where the payer was not charged.
