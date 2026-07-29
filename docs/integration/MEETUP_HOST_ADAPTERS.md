# Meetup Host Adapters

## Host role

The Meetup application is now the Titan Zero host. It supplies shared platform services to WorkCore and optional extensions while retaining ownership of chat, voice-ready conversations, channels, presence and user-facing interaction.

## Company tenancy

`App\\Titan\\Tenancy` provides:

- `ActiveCompanyContext`
- `CompanyContextResolver`
- `EnsureActiveCompany`
- server-side company switching
- request-scoped WorkCore tenant and operation context

Context is resolved from an authenticated active membership and validated server state. Body input is not used as company authority. The middleware clears scoped WorkCore state in a `finally` block after each request.

## Permissions

`PermissionResolver` evaluates owner status, role permissions and member-specific permissions inside the active company. AI agents and extensions consume delegated permissions through host contracts.

## Titan Vault

`DatabaseVault` stores encrypted secrets in `titan_vault_secrets` using Laravel Crypt. Company settings contain only credential references. AI provider keys and Maps provider keys are resolved only inside an authorised company scope.

## Audit

`DatabaseAuditRecorder` writes immutable records with company, user, agent, conversation, action, entity, before and after state, reason, correlation and causation identifiers.

## Capabilities and extensions

The host capability registry supplies the executable allowlist used by Titan Zero. The extension manager validates manifests, compatibility and duplicate capabilities before booting enabled providers.

## WorkCore adapters

Meetup provides tenant, permission, entitlement, menu, storage, notification and tool-bridge adapters. WorkCore remains unaware of donor-platform models and does not own host identity.

## WorkCore read boundary

`ReadModelExecutor` resolves only registered WorkCore read definitions and enforces host company context, capability entitlement, delegated permissions and bounded pagination. Titan Zero and the protected API use the same executor.

## Maps adapters

The Maps integration receives:

- host company context
- Vault credential resolution
- permission decisions
- audit recording
- private export storage
- capability registration
- WorkCore lookup and governed promotion gateways

Candidate promotion never writes WorkCore tables directly.

## AI boundary

`TitanZeroOrchestrator`, `ConversationContextBuilder` and `ToolRouter` enforce:

- company-scoped conversations
- Vault-only provider credentials
- registered-tool-only execution
- safe provider error mapping
- explicit confirmation for governed actions
- no storage of hidden reasoning

## Chat hardening

- Conversations and user discovery are company scoped.
- Presence uses `online.{companyId}`.
- Private user broadcasts include company identity.
- Conversation channels verify both company and participant membership.
- Attachments use private storage and temporary signed download routes.
- Public maintenance routes were removed.
- The operations UI is available to company members; platform administration remains restricted.
