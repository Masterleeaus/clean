# Phase 02 Delta — Chatbot Authority and Compatibility Boundary

## Date

2026-08-02

## Branch

`feature/titan-five-app-pass2-authority`

## Objective

Make one chatbot extension tree authoritative and ensure the parallel legacy tree cannot boot or be discovered as an installable extension.

## Files Modified

- `app/Extensions/Chatbot/config/titan_project_architecture.php`
  - declares the canonical path and provider;
  - declares the legacy path and provider;
  - disables legacy boot;
  - defines the temporary compatibility mode.

- `app/Extensions/TitanZeroChatbot/extension.json`
  - changed from an installable `extension` manifest to `legacy-disabled`;
  - sets `enabled` and `bootable` to false;
  - removes the provider reference;
  - points to the canonical extension.

- `app/Extensions/TitanZeroChatbot/System/ChatbotServiceProvider.php`
  - removes the canonical chatbot namespace from the duplicate tree;
  - becomes a minimal legacy namespace guard;
  - throws if explicitly registered;
  - registers no routes, migrations, views, assets or services.

## Files Created

- `app/Extensions/Chatbot/extension-authority.json`
- `tests/Feature/TitanArchitecture/ChatbotExtensionAuthorityTest.php`
- `.titan/reports/chatbot-authority-boundary.md`
- `.titan/deltas/phase02.md`

## Files Deleted

None.

## Database Changes

None.

## Route Changes

None.

## Migration Changes

None.

## PWA Changes

None.

## AI or WorkCore Changes

None.

## Compatibility

The legacy directory remains available for source comparison but is no longer represented as an installable extension. Existing canonical chatbot routes, provider bindings, migrations, policies, sync services, AI runtime and PWA assets are unchanged.

## Verification

Static syntax validation performed for:

- legacy provider PHP;
- authority configuration PHP;
- authority contract test PHP;
- canonical authority JSON;
- legacy disabled manifest JSON.

All checked files passed syntax parsing.

A repository contract test was added, but the full Laravel test suite could not be executed through the GitHub connector. CI or a checked-out environment must execute:

`php artisan test tests/Feature/TitanArchitecture/ChatbotExtensionAuthorityTest.php`

## Exit Condition

Pass 2 is ready when the PR confirms only these scoped files changed and the authority contract remains syntactically valid.

## Next Pass

Pass 3 will create the canonical registry containing exactly:

1. Titan Zero
2. Titan Go
3. Titan Launch
4. Titan Desk
5. Titan Hub

Legacy app slugs will be mapped explicitly rather than silently removed.
