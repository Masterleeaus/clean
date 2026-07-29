# Titan Money + Titan Pay Source Provenance

**Prepared:** 30 July 2026, Australia/Sydney  
**Repository:** `Masterleeaus/clean`  
**Branch:** `agent/titan-money-pay-chat-upgrade`

## Authoritative base

- Repository: `Masterleeaus/clean`
- Default branch: `main`
- Application position: MagicAI v10.91 host at repository root
- Operational authority: `app/Domains/WorkCore`
- Conversation surface: existing Titan Zero Chatbot extension and host communication layer
- Extension inventory: existing imported Titan Zero extension packages remain part of the base

## Staged upgrade source

| Field | Value |
|---|---|
| Original archive | `TitanZero-Meetup-TitanMoney-TitanPay-v0.5.0-FULL.zip` |
| Original ZIP SHA-256 | `7758320397db5d3e180fbc7b27e831a9a8590769dd6a11de45357d6eb73ec858` |
| Original ZIP bytes | `984427` |
| Transport type | Unchanged original ZIP, published as one unlisted binary asset |
| Transport asset | `https://titan-v050-binary-transport.miniup.app/TitanZero-Meetup-TitanMoney-TitanPay-v0.5.0-FULL.zip` |
| Drive backup file ID | `18L3L4osTR_fuc21bw1PJroCuw8-GuxWb` |
| Extracted archive files | `404` |
| Staging destination | `source-packs/titan-money-titan-pay-v0.5.0/` |
| Runtime status | Donor/reference source only until dispositioned |

The import workflow verifies the SHA-256 checksum, ZIP integrity, exact archive file count, expected wrapper directory and both domain service providers before committing extracted files.

## Relevant project references

- `WorkCore Technical Architecture Specification.txt`
- `Workcore.txt`
- `Extension System.txt`
- `TitanZero-Extension-SDK-v2.0.0(1).zip`
- `MagicAI-v10.91-WORKCORE-MERGED.zip`
- `Titan-Zero-Chatbot-PWA-PASS12-HOST-BOUNDARY-FIXED(1).zip`
- `Base App System Extensions.zip`
- `AI System Extensions.zip`
- `Marketing & Creative Extensions.zip`
- `Modules for Titan BOS.zip`

These references are not to be duplicated blindly. The implementation pass must compare them against files already present in `main` and import only missing or stronger code.

## v0.5.0 functional scope

The staged source contains the prior cumulative work for:

- `TitanMoney` bounded domain
- `TitanPay` bounded domain
- governed auto-invoice and receivables agents
- job-completion intent parsing
- WorkCore job gateway contracts
- invoice issue and reminder automation
- PayID, bank transfer, cash and PayPal collection paths
- secure collection links and QR generation
- customer Channels, email, SMS/webhook and optional voice handoff
- delivery receipts and internal exceptions
- payment verification and reconciliation boundaries
- multi-company controls, audit context and outbox processing

## Excluded donor behaviour

The target integration must not restore:

- InvoixPro browser-return payment confirmation
- remote provisioning or remote application-disable logic
- destructive web installers
- executable ZIP add-on installation
- plaintext API keys
- public payment evidence
- standalone user-scoped tenancy
- duplicate Finance or ZeroPay namespaces
- duplicate WorkCore, chatbot or authentication authorities

## Trust boundary

The staged archive is evidence of implementation work, not proof that it is compatible with the clean host. Every staged file requires path, dependency, tenancy, security and ownership review before it is moved into runtime.
