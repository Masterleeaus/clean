# Property, Tenancy and Accommodation Execution Design

## Goal
Extend canonical WorkCore Premises with governed long-term property occupancy and short-stay accommodation operations without introducing a second property, tenancy, finance, identity or permission authority.

## Authority
- `tz_premises` and `tz_premises_spaces` remain the physical-location authority.
- WorkCore owns party roles, agreements, occupancies, access authorisations, reservations, stays, housekeeping and operational folio charges.
- CRM remains the authority for customers and contacts.
- Titan Vault remains the authority for access secrets.
- Finance/ZeroPay remains the authority for invoices, payments, trust money and settlement.
- Meetup remains the communication/UI layer; Titan Zero invokes registered WorkCore actions and reads.

## Data model
Long-term operations use company-scoped `tz_premises_party_roles`, `tz_premises_agreements`, `tz_premises_occupancies` and `tz_premises_access_authorisations`.
Accommodation uses `tz_accommodation_rate_plans`, `tz_accommodation_reservations`, `tz_accommodation_reservation_spaces`, `tz_accommodation_guests`, `tz_accommodation_stays`, `tz_accommodation_housekeeping_tasks`, `tz_accommodation_charges` and `tz_accommodation_channel_references`.

All public references use ULIDs. All joins retain `company_id`. Reservation windows are half-open `[arrival, departure)` so a new arrival may begin at the previous departure instant.

## Workflows
Long-term: register party role → create agreement → allocate occupancy → authorise access → end occupancy/agreement.
Short-stay: configure rate plan → search availability → create/confirm reservation → check in guest → create occupancy/access → check out → dirty/turnover housekeeping → ready.

## State and safety rules
- Agreements cannot activate without valid dates and premises.
- Active occupancy cannot overlap another capacity-consuming occupancy in the same space.
- Confirmed or checked-in reservations consume availability.
- Check-in requires a ready space unless an explicit override reason is supplied.
- Checkout closes only records marked `managed_by_accommodation`; pre-existing tenancy records survive.
- Charges are immutable operational folio entries; corrections use reversing entries.
- Every write runs through BusinessActionDispatcher with permissions, confirmation, idempotency, audit and domain events.

## Reads
Expose agreement profile, occupancy board, accommodation availability, accommodation board and reservation folio through the existing governed read executor.

## Interfaces
Register actions and reads in `WorkPremisesServiceProvider`; add capability `workcore.accommodation`; use the existing protected generic Titan action/read APIs. No separate public donor routes are imported.

## Testing
Dependency-free domain tests cover state transitions, overlap, nights and Australian-dollar rate calculations. Structural tests verify tenancy columns, foreign keys, provider registrations and the absence of direct finance/payment writes. Full Titan verifier, namespace scan, AI tests and Maps tests must remain green.

## Exclusions
No rent ledger, bond/trust accounting, invoice settlement, channel API synchronisation, dynamic pricing, owner statements or legal-form generation in this pass. Those belong to later finance and integration passes.
