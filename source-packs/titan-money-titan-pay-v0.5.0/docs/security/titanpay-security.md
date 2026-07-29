# Titan Pay security controls

- Payment completion is server-to-server only.
- Browser success/return routes cannot mark invoices paid.
- Webhook URLs identify the exact gateway connection, not merely a provider name.
- Stripe signatures use timestamp tolerance and HMAC comparison.
- PayPal webhooks use provider verification APIs.
- Only events classified as completed payments proceed to reconciliation.
- Payment amount and currency must exactly match the collection session.
- Provider event IDs and transaction IDs are unique and idempotent.
- Collection public tokens are random and hashed at rest.
- Payment evidence uses a private filesystem disk and controlled download route.
- Company context and granular company permissions protect authenticated records.
- Issued invoices cannot be edited; corrections require void, credit or reissue workflows.
- Gateway credentials are represented by Vault references. Environment resolution is a development fallback, not the production secret authority.
