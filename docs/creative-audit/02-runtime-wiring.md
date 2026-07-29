# Scan 2 — Runtime Wiring (Active)

Status: In progress
Date started: 2026-07-30

## Baseline validation

- Extracted PHP files linted: 208
- PHP syntax failures: 0
- Validation command: recursive `php -l` across all six extracted extension roots

This proves parseability only. It does not prove route registration, service-container resolution, migration compatibility, authorisation, queue execution or frontend reachability.

## Service-provider entry points

| Extension | Provider | Registration pattern |
|---|---|---|
| Advanced Image | `System/AdvancedImageServiceProvider.php` | views, translations, routes and migrations; no config binding in `register()` |
| AI Image Pro | `System/AIImageProServiceProvider.php` | config, views, routes, migrations, assets and components; marketplace register/uninstall contracts |
| AI Photoshoot | `System/AIPhotoshootServiceProvider.php` | provider/factory/model-registry architecture; detailed trace pending |
| Creative Suite | `System/CreativeSuiteServiceProvider.php` | config, views, routes, migrations and published editor assets |
| Creative Suite AI Template | `System/CreativeSuiteAITemplateServiceProvider.php` | config, views and two Creative Suite-prefixed routes |
| Creative Suite Annotations | `System/CreativeSuiteAnnotationsServiceProvider.php` | config, views, routes, optional migrations and published assets |

## Verified route boundaries

### Creative Suite

User routes are grouped under:

- prefix: `dashboard/user/creative-suite`
- middleware: `web`, `auth`
- route-name prefix: `dashboard.user.creative-suite.`

The provider exposes document create/update, duplicate, rename, delete and show operations; image upload; AI editor dispatch; and task status polling.

Admin settings are grouped under `dashboard/admin/creative-suite`, but the provider-level group currently shows only `web` and `auth`. Controller-level authorisation must be verified before classifying this as safe.

### Creative Suite AI Template

The add-on correctly attaches to the Creative Suite route family:

- `POST dashboard/user/creative-suite/ai/generate-template`
- `GET dashboard/user/creative-suite/ai/generate-template/{taskId}/status`

Both routes use `web` and `auth`. Plan/entitlement enforcement and object ownership checks remain to be traced.

### Creative Suite Annotations

User routes are isolated under `dashboard/user/creative-suite-annotations` and include `CheckTemplateTypeAndPlan` in addition to `web` and `auth`.

Admin settings are isolated under `dashboard/admin/creative-suite-annotations` and explicitly apply `admin` middleware.

### Advanced Image

User routes are under `dashboard/user/advanced-image` and use `web` and `auth`.

Provider settings routes are registered under `dashboard/admin/settings`, but no explicit `admin` middleware is visible in the service provider. Controller or global-route enforcement must be verified.

The webhook route is outside the authenticated web group:

- `ANY api/webhook/advanced-image/{model?}`
- middleware: `api`

Webhook signature verification, accepted methods, replay protection and model allow-listing are priority checks. The use of `ANY` broadens the attack surface unless required by every upstream provider.

## Early architectural observations

1. The two Creative Suite add-ons attach through route prefixes rather than an explicit extension capability contract. This works, but creates hidden coupling to route names, task identifiers and editor assets.
2. Admin-route protection is inconsistent at provider level. An audit must distinguish controller-authorised routes from routes relying only on authentication.
3. AI task status polling appears in at least four extensions. A shared job-state contract may remove duplicated polling semantics without merging the products.
4. Provider routing is split: Advanced Image has multiple concrete provider services, while AI Photoshoot has an explicit provider interface/factory/registry. The latter is the stronger base for a shared provider capability contract.
5. Uninstall hooks are present in some providers but empty. Migration/data/asset cleanup behaviour therefore cannot be assumed.

## Next trace targets

1. Controllers and form requests for ownership, tenant and permission enforcement.
2. Queue payload serialisation, retry policy, idempotency and status storage.
3. Webhook controller authentication and task-state mutation.
4. Migration table/index names and collision risks for duplicated conversions.
5. Frontend route-name and asset-path dependencies between Creative Suite and its add-ons.
