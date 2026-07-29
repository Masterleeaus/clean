# Mobile Builder Donor Register

The donor archives were inspected and extracted outside the runtime tree. They are not committed as active application code in Pass 0.

## Approved adaptation sources

- AppForge: device previews, branding controls, navigation configuration, QR/install guidance, push setup, asset upload, and keyboard-shortcut patterns.
- MobileKit: headers, bottom navigation, cards, forms, action sheets, toasts, dark/RTL tokens, profile/chat/invoice layouts, and add-to-home patterns.
- Online app builder: selected Vue builder and Flutter shell patterns for future reference.

## Prohibited authority imports

- Supabase, donor authentication, donor billing/subscriptions, donor database models, donor storage authority, donor service workers/manifests, unsandboxed plugins, public webhooks, direct operational writes, and embedded provider secrets.

The existing Titan Zero chatbot PWA remains the only PWA runtime. Donor code is adapted task-by-task after tests establish the required behaviour.
