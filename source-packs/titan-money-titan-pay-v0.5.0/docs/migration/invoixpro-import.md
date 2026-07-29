# Importing legacy InvoixPro data

The donor application is not overlaid onto Meetup. Its supported data can be imported through an isolated database connection.

1. Back up both databases.
2. Configure a Laravel database connection named `invoixpro`.
3. Run a dry inspection:

```bash
php artisan titan-money:import-invoixpro --company=<TITAN_COMPANY_ULID>
```

4. Review counts and conflicts.
5. Commit the import:

```bash
php artisan titan-money:import-invoixpro --company=<TITAN_COMPANY_ULID> --commit
```

Use `--legacy-user=<id>` when one InvoixPro database contains several independent businesses.

Legacy payment logs are imported as **claimed**, not verified. They require reconciliation because the donor payment-success flow did not provide sufficient cryptographic evidence.
