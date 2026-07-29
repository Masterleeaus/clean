# Titan Money v0.2 rename note

Version 0.2.0 replaces the former generic `Finance` bounded-context name with **Titan Money**.

Clean installations create only `titan_money_*` tables and use the `App\Domains\TitanMoney` namespace.

When upgrading source code from v0.1.0:

1. Back up the application and database.
2. Delete the paths listed in the delta package's `DELETE_PATHS.txt`.
3. Overlay the v0.2.0 delta.
4. Do not run the new migrations against an existing database containing `finance_*` data until those tables and foreign-key columns have been migrated to `titan_money_*` names.
5. Clear cached configuration, routes and views.
6. Run the complete test suite before enabling payment gateways.

The v0.2.0 package is primarily intended for the current pre-deployment Titan Zero integration workspace. It does not silently rename production financial tables because financial migrations require an explicit backup and rollback plan.
