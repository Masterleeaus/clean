# Titan Train porting ledger

## Old branch and provenance

- Reference branch: `agent/titan-train-lms`
- Reference head: `fb370a9e9860bec3ec7b5fbe579cc5b4b9eb6b58`
- Old PR: #11, closed and merged
- Classification: Category D by ancestry; Category A by absorbed behaviour

## Files intentionally ported

No files or commits were copied from the old branch.

The current increment was recreated against current main and adds only:

- Reconciliation status and handoff documentation
- A new domain-local Titan Train interaction-definition catalog
- Unit contracts for authority, safety and confirmation boundaries

## Files deliberately rejected

- Old `config/app.php` provider registration
- Old branch ancestry and commits
- Donor archives
- Generated dependencies and runtime data
- Duplicate registries or providers
- Offline LMS persistence

## Existing main functionality preserved

- Current staged provider boot
- Canonical Interaction Engine package
- Titan Train learning-record authority
- WorkCore operational authority
- Native Chatbot learner workspace
- Titan Channels communication authority

## Next decision

The catalog remains unregistered until the coordinator approves the shared provider/registry lock and the current canonical compiler/contribution contract is mapped.
