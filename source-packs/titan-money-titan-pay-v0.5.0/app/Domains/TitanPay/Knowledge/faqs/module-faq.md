# Titan Pay Module — Operator FAQs

## General

**Q: What is Titan Pay?**
A: Titan Pay is a multi-gateway payment module. It allows operators to accept payments via Stripe, PayPal, bank transfer, PayID, cryptocurrency, and cash through a unified session-based flow.

**Q: How are payments tenant-isolated?**
A: Every model in Titan Pay (`Titan PaySession`, `Titan PayTransaction`, `Titan PayBankDeposit`, etc.) applies a `TenantScope` that filters all queries by `company_id`. Cross-company data access is impossible at the query level.

## Sessions

**Q: How long is a payment session valid?**
A: Sessions default to 30 minutes from creation. The `expires_at` field can be set at session creation time if a different expiry is required. Expired sessions cannot be reactivated.

**Q: Can I reopen an expired session?**
A: No. Once a session is `expired`, a new session must be created. The previous session is soft-deleted after the retention period.

**Q: What does the `processing` status mean?**
A: `processing` means the gateway has received a payment confirmation (e.g. webhook from Stripe) but final settlement has not yet been confirmed. The session is advanced to `completed` once settlement is verified.

**Q: Why is a session stuck in `pending`?**
A: The most common causes are: (1) the payer has not initiated payment, (2) the gateway is experiencing delays, (3) the webhook was not received. Check the `Titan PayGatewayLog` for gateway communication details.

## Transactions

**Q: What is the difference between a session and a transaction?**
A: A session is the payment intent — it holds the amount, currency, gateway, and status. A transaction is the record of an actual fund movement confirmed by the gateway. A session may have multiple transactions if partial payments or retries occur.

**Q: Where is the gateway fee recorded?**
A: The `fee` and `net_amount` fields on `Titan PayTransaction` record the gateway fee deducted and the net amount received by the operator.

## Bank Transfers

**Q: How do I match a bank deposit to a session?**
A: Titan Pay auto-matches deposits with a confidence score ≥ 80. Deposits below threshold appear in the admin panel under "Unmatched Deposits" for manual review. See the Bank Matching Guide for full details.

**Q: Can I import bank statements?**
A: Yes. Titan Pay supports CSV import of bank statements. Imported deposits are created as `Titan PayBankDeposit` records and go through the same matching process as real-time deposits.

## Refunds

**Q: How do I process a refund?**
A: Refunds are processed per the Refund Policy. For card gateways (Stripe, PayPal), refunds are initiated via the gateway adapter's `refundPayment()` method. Bank transfer and cash refunds are processed manually and recorded as a refund transaction.

**Q: Is the refund processed automatically?**
A: For Stripe and PayPal, the refund call is made to the gateway immediately upon operator confirmation. For other gateways, operators complete the refund manually and mark it complete in the admin panel.

## Security

**Q: Will the AI agent ever show me full card numbers or bank account details?**
A: No. The Titan Pay AI guardrails explicitly block exposure of card numbers, CVV, full bank account numbers, BSBs, gateway API keys, and other payment credentials. Only masked or reference values are returned.

**Q: Who can use the Titan Pay agent?**
A: Users with the `titanpay.reconciliation.resolve` permission. Operators can manage this permission in the company permission settings.
