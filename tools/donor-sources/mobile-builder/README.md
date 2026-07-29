# Mobile Builder Donor Sources

These files are quarantined reference material for the Titan Zero chatbot PWA upgrade.

## Included

- `appforge-mobilekit/` — the verified 303-file AppForge and MobileKit donor extraction.
- `website-app-builder/vue/` — Vue builder interface and visual configuration patterns.
- `website-app-builder/flutter/` — Flutter WebView shell and navigation/offline UI reference.
- `website-app-builder/backend-reference/` — selected project-build, design, navigation, branding and preview controllers.

## Rules

- Nothing under `tools/donor-sources` is autoloaded, compiled, routed or deployed.
- Donor authentication, subscriptions, payments, Supabase, CodeIgniter authority, service workers, webhooks and storage models are not adopted.
- WorkCore remains the operational authority.
- MagicAI remains the Laravel host and desktop authority.
- `app/Extensions/Chatbot` remains the canonical fourteen-app PWA.
- Code must be reimplemented behind Titan contracts and tests before entering runtime paths.
