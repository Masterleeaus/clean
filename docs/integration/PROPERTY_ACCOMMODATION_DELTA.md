# Property and Accommodation Delta

## Purpose

This pass converts the useful property, tenancy, residency and accommodation concepts found in the quarantined WorkCore and Titan BOS donor material into canonical WorkCore runtime capabilities.

The donor modules were treated as behavioural references and delta sources. They were not copied wholesale into runtime autoload.

## Authority decision

### Canonical authority retained

- **Premises** owns physical properties, buildings, units, rooms, spaces and physical availability.
- **CRM** owns customers and contacts.
- **WorkCore Property records** own party roles, agreements, occupancies and access authorisations connected to premises.
- **WorkCore Accommodation** owns transient reservations, stays, guests, rates, housekeeping state, operational folio charges and channel references.
- **Titan Zero** interprets user intent and invokes registered WorkCore actions and reads.
- **Meetup** owns chat, realtime communication, user interaction and the Operations interface.
- **Titan Money / ZeroPay** remains the future authority for invoices, payments, deposits, refunds, settlement and trust accounting.

### Rejected duplicate authorities

The following donor concepts were not activated as independent runtime authorities:

- duplicate companies, users, roles or tenancy
- duplicate properties, rooms or assets
- separate landlord, tenant or guest identity stores
- module-local invoices, payments, ledgers or trust accounts
- standalone authentication, routes, dashboards or file storage
- donor service containers that bypass WorkCore action and read registries
- direct database writes from Titan Zero or extension code

## Accepted domain model

### Shared property execution

- premises party roles
- agreements
- occupancies
- access authorisations

These records support owners, residents, tenants, occupants, guests, agents, authorised visitors and other premise-linked roles without creating a second CRM authority.

### Accommodation execution

- rate plans
- reservations
- reservation-space allocations
- guests
- stays
- housekeeping tasks
- immutable operational folio charges
- channel references

Accommodation uses existing Premises spaces. It does not create a second room or unit catalogue.

## Lifecycle rules

### Agreements

Agreement status changes use an explicit state policy. Invalid transitions are rejected.

### Occupancies

Long-term occupancy and transient reservations share conflict checks. The same space cannot be double-booked across the two operating models.

### Check-in

A confirmed reservation may create accommodation-managed:

- party role
- short-stay agreement
- occupancy
- access authorisation
- stay record

### Checkout

Checkout may close only records marked `managed_by_accommodation`. Pre-existing tenancy or externally managed occupancy remains active.

Checkout also:

- closes the stay
- moves the reservation to checked out
- marks the allocated space unavailable for turnover
- creates a dirty housekeeping task

### Housekeeping

Housekeeping follows a controlled state machine. A completed and inspected turnover can return the space to ready status.

### Folio charges

Accommodation charges are operational records, not accounting authority. Entries are immutable. Corrections are represented by linked negative reversals rather than editing or deleting history.

## Governed execution

### Registered actions

- `workcore.premises.party_role.create`
- `workcore.premises.agreement.create`
- `workcore.premises.agreement.status`
- `workcore.premises.occupancy.allocate`
- `workcore.premises.occupancy.end`
- `workcore.accommodation.rate_plan.upsert`
- `workcore.accommodation.reservation.create`
- `workcore.accommodation.reservation.status`
- `workcore.accommodation.stay.check_in`
- `workcore.accommodation.stay.check_out`
- `workcore.accommodation.housekeeping.update`
- `workcore.accommodation.charge.add`

All writes pass through WorkCore confirmation, idempotency, tenant context, permissions, audit and domain-event infrastructure.

### Registered reads

- `workcore.premises.agreement.profile`
- `workcore.premises.occupancy.board`
- `workcore.accommodation.availability`
- `workcore.accommodation.board`
- `workcore.accommodation.folio`

Reads use the canonical WorkCore read executor, capability entitlements, delegated permissions, server-resolved company scope and bounded pagination.

## Capability and permissions

The new `workcore.accommodation` capability controls transient accommodation operations.

Premises permissions add:

- `manage_party_roles`
- `manage_agreements`
- `manage_occupancies`

Accommodation permissions include:

- `view`
- `manage_rates`
- `manage_reservations`
- `check_in`
- `check_out`
- `housekeeping`
- `charges`

## Data integrity

- Every new operational table is company scoped.
- Public aggregate identifiers are retained in action results.
- Internal database IDs are removed from public snapshots.
- Reservation and occupancy overlap checks are tenant scoped.
- Folio entries are append-only.
- Checkout cannot terminate non-accommodation-managed tenancy.
- Currency defaults to Australian dollars (`AUD`) but is recorded explicitly on rates and charges.

## Remaining depth

This pass does not claim complete jurisdiction-specific property management. Future specifications are still required for:

- Australian state-specific residential tenancy notices and forms
- owner statements and management agreements
- bond authority integrations
- rent schedules, arrears and trust accounting
- channel-manager connectivity and OTA restrictions
- dynamic pricing and revenue management
- owner portals, tenant portals and guest self-service
- regulated accommodation reporting
- finance settlement through Titan Money / ZeroPay
