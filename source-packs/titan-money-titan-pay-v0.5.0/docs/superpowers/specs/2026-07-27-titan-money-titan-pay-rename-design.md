# Titan Money and Titan Pay Rename Design

## Decision

Rename the native `Finance` bounded context to `Titan Money` while preserving `Titan Pay` as the payment execution bounded context.

## Runtime naming

- PHP namespace: `App\Domains\TitanMoney`
- Domain directory: `app/Domains/TitanMoney`
- Service provider: `TitanMoneyServiceProvider`
- URL prefix: `/titan-money`
- Route-name prefix: `titanmoney.`
- API prefix: `/api/titan-money/v1`
- View namespace: `titanmoney::`
- Config key and file: `titanmoney`
- Console command prefix: `titan-money:`
- Permission prefix: `titanmoney.`
- Environment prefix: `TITAN_MONEY_`
- Database table prefix: `titan_money_`
- Titan Pay foreign-key column: `titan_money_payment_id`

## User-facing naming

All navigation, headings, manifests, documentation and package names use `Titan Money` and `Titan Pay`. Generic descriptive phrases such as “financial records” may remain where they are not system identifiers.

## Compatibility

This v0.2.0 package is a pre-production rename of v0.1.0. Existing `finance_*` tables are not retained as parallel authorities. The packaged migrations create only `titan_money_*` tables. An explicit upgrade migration is required only for installations that already deployed v0.1.0 data.

## Security invariants

The rename must not weaken the existing controls: browser redirects cannot confirm payments; gateway events remain signed and idempotent; amount, currency and merchant connection checks remain mandatory; payment evidence remains private; all records remain company-scoped.
