# WorkCore Repair Report

## Result

WorkCore has been installed as a first-party domain at `app/Domains/WorkCore`, not as a detachable extension. The repaired runtime contains 487 PHP files and 20 registered operational module providers. A simple declaration scan identifies 458 top-level classes, interfaces, traits or enums.

## Repairs performed

### Namespace authority

- Rebased canonical classes from `App\\Extensions\\WorkCore` to `App\\Domains\\WorkCore`.
- Removed runtime dependence on the former extension identity.
- Added one authoritative `WorkCoreServiceProvider` to the Laravel provider list.
- Verified that the runtime contains no unresolved internal `App` imports through the dependency-free verifier.

### Host adaptation

Seven Meetup adapters provide the host contracts required by WorkCore:

- `MeetupTenantResolver`
- `MeetupPermissionResolver`
- `MeetupEntitlementResolver`
- `MeetupMenuAdapter`
- `MeetupStorageAdapter`
- `MeetupNotificationAdapter`
- `MeetupToolBridge`

These adapters prevent WorkCore from inventing a second user, company, permission, storage or notification authority.

### Enabled module providers

The initial runtime registers:

1. CRM
2. Premises
3. Catalogue
4. Operations
5. Scheduling
6. Dispatch
7. Workforce
8. Rosters
9. Assets
10. Inventory
11. Forms
12. Attendance
13. Support
14. Knowledge base
15. Reviews
16. Supply
17. Fleet
18. Repairs
19. Documents and Evidence
20. Assurance

Trade and field compliance is registered through its policy and migration layer rather than as a separate module provider.

### Migration repair

Twenty-nine WorkCore migrations were selected and normalised into the host migration sequence. They cover:

- CRM and invitations
- governed actions, confirmation, idempotency, audit and outbox
- premises and service locations
- service catalogue
- work orders and tasks
- appointments and dispatch
- workers, availability and rosters
- assets, equipment, inventory and materials
- forms, attendance and leave
- documents, immutable versions, evidence, approvals and sign-offs
- inspections, findings, corrective actions, risks and incidents
- support, knowledge and reviews
- supply, fleet and repairs
- vertical records and trade compliance

The host company tables are not duplicated by WorkCore migrations.

### Vertical metadata

Twenty vertical manifests are retained under `resources/workcore/verticals`, including property management, cleaning, field services, facilities, real estate, short stay, accommodation, plumbing, electrical, handyman, gardening and pressure washing.

These manifests describe extension points. Their presence does not imply that every specialist workflow has a complete user interface or production-tested execution path.

## Quarantined WorkCore areas

- Donor `IntegratedSources`
- Embedded Titan Rewind branches
- Incomplete Intelligence runtime
- Finance and Titan Money ownership requiring a separate canonical merge
- Accommodation and NDIS execution paths with unresolved or unverified dependencies
- Native AI tools whose action handlers or read models are absent

## Current operational boundary

The host exposes summaries, governed actions and governed read models through `routes/titan.php`. Direct WorkCore module routes remain disabled in configuration. This keeps all mutations behind the host company context, permissions, confirmation, idempotency and audit boundary.

## Verification evidence

- Full dependency-free verifier results are recorded in the current build report.
- Titan AI standalone suite: 8 passed.
- Titan Maps standalone suite: 32 passed.
- PHP syntax validation is performed across runtime roots by `tools/titan_verify.php`.

Laravel service-provider boot and database migration execution still require installed Composer dependencies and a configured test database.
