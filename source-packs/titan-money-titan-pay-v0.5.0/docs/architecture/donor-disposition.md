# InvoixPro and ZeroPay donor disposition

- InvoixPro meaningful files inspected: **468**
- ZeroPay meaningful files inspected: **397**

| Source | Disposition | Titan target / reason |
|---|---|---|
| `InvoixPro/app/Services/InvoiceService.php` | **PORT_WITH_REWRITE** | Titan Money invoice services and precise minor-unit calculations |
| `InvoixPro/resources/views/invoices/*` | **UI_REFERENCE_ONLY** | Native Titan Money Blade views and PDF template |
| `InvoixPro/app/Models/RecurringInvoice.php` | **PORT_WITH_REWRITE** | Titan Money recurring invoice aggregate |
| `InvoixPro/app/Services/InvoiceReminderService.php` | **PORT_WITH_REWRITE** | Titan Money reminder scheduling and outbox |
| `InvoixPro/customer portal` | **PORT_WITH_REWRITE** | Titan Pay public collection and claims views |
| `InvoixPro payment success controller` | **REJECT_SECURITY** | Browser redirect cannot confirm payment |
| `InvoixPro provisioning/licensing runtime` | **REJECT_SECURITY** | Remote runtime dependency and disable path |
| `InvoixPro installer` | **REJECT_SECURITY** | Destructive migration and fixed credentials |
| `InvoixPro add-on ZIP installer` | **REJECT_SECURITY** | Executable archive extraction |
| `InvoixPro API key authentication` | **REJECT_SECURITY** | Plaintext unscoped persistent keys |
| `ZeroPayModule payment sessions` | **PORT_WITH_REWRITE** | Titan Pay collection sessions |
| `ZeroPayModule gateway adapters` | **PORT_WITH_REWRITE** | Titan Pay adapters without Filament dependencies |
| `ZeroPayModule knowledge files` | **PORT_WITH_REWRITE** | Curated and renamed Titan Pay knowledge |
| `ZeroPayModule Filament resources` | **REJECT_ARCHITECTURE** | Meetup uses native Blade application shell |
| `ZeroPayModule namespace/table names` | **REJECT_ARCHITECTURE** | Renamed to TitanPay/titanpay_* |
