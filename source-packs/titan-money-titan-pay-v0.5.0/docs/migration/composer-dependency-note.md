# Optional invoice PDF dependency

Titan Money always stores an immutable HTML invoice snapshot. The core package does not require a PDF library, and the supplied `composer.json` and `composer.lock` remain consistent for `composer install`.

To enable server-rendered PDF invoices, add the optional package in the deployment repository and commit the regenerated lock file:

```bash
composer require barryvdh/laravel-dompdf:^3.1
```

Then run the complete Laravel test, migration, PDF rendering and cache verification commands before deployment.
