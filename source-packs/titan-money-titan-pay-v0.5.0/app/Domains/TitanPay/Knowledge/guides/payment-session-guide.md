# Payment Session Guide

## Overview

A Titan Pay payment session is the core unit of a payment transaction. Every payment flow begins with creating a session, which holds all the information required to process a payment through the selected gateway.

## Session Lifecycle

A session passes through the following statuses:

| Status | Description |
|--------|-------------|
| `pending` | Session created; awaiting gateway initialisation |
| `opened` | Gateway confirmed; awaiting payer action (e.g. scan QR, redirect) |
| `processing` | Payment received by gateway; confirming settlement |
| `completed` | Payment settled; associated transaction recorded |
| `failed` | Gateway reported failure or payment was declined |
| `expired` | Session exceeded `expires_at` timestamp without completion |

## Creating a Session

Sessions are created via `CreateTitan PaySessionAction`. Required fields:

- **`company_id`** — tenant identifier; always inferred from authenticated context
- **`user_id`** — user initiating the payment
- **`gateway`** — the payment gateway slug (see Gateway Selection Guide)
- **`amount`** — decimal value with two decimal places (e.g. `50.00`)
- **`currency`** — ISO 4217 code (e.g. `AUD`, `USD`)
- **`expires_at`** — optional; defaults to 30 minutes if not provided

On creation a `Titan PaySessionCreated` event is dispatched, which triggers gateway initialisation and QR code generation where applicable.

## Session Token

Each session is identified by a unique `session_token` (UUID-style slug). This token is the public-facing identifier used in URLs, QR payloads, and API lookups. Internal numeric `id` values must not be exposed externally.

## Expiry

Sessions that are not completed before `expires_at` are automatically transitioned to `expired` by the `ExpireTitan PaySessionsJob` scheduled command. Expired sessions cannot be reopened — a new session must be created.

## Retrieving a Session

Use `LookupTitan PaySessionAction` to retrieve a session by token or ID. All queries are tenant-scoped via `TenantScope` on the `Titan PaySession` model; cross-company access is not possible.

## Related Models

- `Titan PayTransaction` — one or more transactions can be linked to a session
- `Titan PayQrCode` — QR code generated for the session (one-to-one)
- `Titan PayGatewayLog` — raw gateway communication log
- `Titan PayNotification` — notifications dispatched for session events
