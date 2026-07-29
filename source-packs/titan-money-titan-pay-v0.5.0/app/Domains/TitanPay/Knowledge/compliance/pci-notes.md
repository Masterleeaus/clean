# Titan Pay PCI DSS Notes

## Scope

These notes summarise the PCI DSS (Payment Card Industry Data Security Standard) considerations relevant to operators running the Titan Pay module. This is not a full PCI DSS compliance program — operators must complete their own assessment with a Qualified Security Assessor (QSA) where required.

## Cardholder Data

Titan Pay does **not** store, process, or transmit raw cardholder data (PANs, CVV, expiry dates) within the application database. Card payment processing is delegated entirely to the Stripe gateway adapter, which tokenises card data client-side before it reaches Titan Pay servers.

- Titan Pay does not store `card_number`, `cvv`, or `expiry_date` in any model.
- The `Titan PayTransaction.meta` field must never be used to store raw card data.
- The `Titan PayGatewayLog` stores only masked or tokenised references from gateway responses.

## SAQ Applicability

Because Titan Pay uses Stripe's client-side tokenisation (Stripe.js / Stripe Elements), operators using only the Stripe gateway typically qualify for **SAQ A** (the lightest PCI DSS self-assessment). Operators using other gateways should verify their own SAQ scope.

## Data Retention

- `Titan PayTransaction` records with gateway references are retained per the operator's data retention policy (minimum 12 months, maximum 7 years recommended for financial records).
- `Titan PayGatewayLog` records are retained for audit purposes for a minimum of 12 months.
- Soft-deleted records (`deleted_at`) are retained in the database but excluded from application queries.

## Sensitive Authentication Data

The following fields are classified as sensitive and must never appear in:
- API responses returned to end-users
- AI agent responses
- Log files or error messages
- Support tickets or exported reports

| Field | Classification |
|-------|---------------|
| Stripe secret key | Sensitive authentication data |
| PayPal client secret | Sensitive authentication data |
| Cryptomus API key | Sensitive authentication data |
| Webhook signing secrets | Sensitive authentication data |
| Bank account number | Sensitive financial data |
| BSB | Sensitive financial data |

These values are stored in environment variables (`.env`) and must not be committed to version control or stored in the database.

## Network Security

- All Titan Pay API endpoints require authentication (`auth:sanctum` middleware).
- Webhook endpoints from gateways must validate signatures before processing.
- All communication between Titan Pay and gateways must occur over TLS 1.2 or higher.

## Vulnerability Management

- Dependencies are reviewed via `composer audit` as part of the CI pipeline.
- Gateway adapter code that handles payment data must be reviewed with each dependency update.

## Incident Response

If a potential card data breach is suspected:
1. Immediately revoke and rotate all gateway API keys stored in `.env`.
2. Notify the affected gateway provider (Stripe, PayPal, etc.) immediately.
3. Log the incident and engage a QSA for forensic assessment if required.
4. Notify affected cardholders per applicable data breach notification laws.
