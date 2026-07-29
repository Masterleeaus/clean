# Architecture Map

## AppForge donor roles

| Area | Titan destination |
|---|---|
| Builder wizard and progress | Titan Mobile Publisher project workflow |
| Device previews | Visual App Designer preview frame |
| Branding and navigation controls | Versioned Titan app specification |
| Build history/status UI | Mobile Publisher build/release centre |
| Asset upload and optimization | Titan asset studio |
| Capacitor/Codemagic configuration | Build-provider adapter references |
| Native hooks | Titan local mobile shell adapters |

## MobileKit donor roles

| Area | Titan destination |
|---|---|
| App header, bottom menu, sidebar | Titan mobile component registry |
| Cards, lists, forms, dialogs | Shared mobile design system |
| Chat/profile/invoice screens | Role-specific template references |
| Dark/RTL/theme Sass | Titan theme token implementation |
| Add-to-home/online detection | PWA interaction references |
| Service worker | Reference only; replace with Titan offline runtime |

## Authority boundaries

Never import donor authentication, billing, tenancy, subscription, credits, permissions, database authority, secrets, public webhooks, or arbitrary executable-code generation.
