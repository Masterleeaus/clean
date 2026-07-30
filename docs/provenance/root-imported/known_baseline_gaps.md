# Known Baseline Gaps

These are recorded before implementation so later passes do not mistake inherited defects for new regressions.

1. `composer.json` adds the local `titanzero/interaction-engine` path package, but the supplied `composer.lock` does not yet contain that package. Pass 1 must run Composer and commit the regenerated lock only after dependency validation.
2. The Interaction Engine is eligible for Laravel package auto-discovery and is also listed manually in `config/app.php`. Pass 1 must select one authoritative registration path and prove the provider loads once.
3. Interaction Engine migrations are split between package migration directories and include August 2026 timestamps. Pass 1 must check ordering and collisions against WorkCore and the reference SQL before running production migrations.
4. The supplied development archive excludes `vendor/` and `node_modules/`; Laravel boot, route, migration, Vite, and browser verification still require dependency installation.
5. The Interaction Engine standalone tests are present, but root host/WorkCore integration coverage remains thin and must be expanded.
6. The existing Chatbot 6.9 PWA already owns the fourteen-app shell, five-tier AI, generative UI, device runtime, and offline WorkCore integration. No parallel replacements are permitted.
