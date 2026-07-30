# Scan 1 — Creative Extension Source Inventory

Date: 2026-07-30
Branch: `agent/creative-extension-upgrade-workspace`

## Verified archive inventory

| Extension | Files | Service providers | Controllers | Jobs | Services | Migrations |
|---|---:|---:|---:|---:|---:|---:|
| Advanced Image | 47 | 1 | 8 | 0 | 6 | 2 |
| AI Image Pro | 151 | 1 | 2 | 2 | 2 | 7 |
| AI Photoshoot | 95 | 1 | 12 | 3 | 1 named `*Service.php`, plus provider/factory/registry classes | 5 |
| Creative Suite | 111 | 1 | 5 | 0 | 0 | 1 |
| Creative Suite AI Template | 10 | 1 | 1 | 1 | 0 | 0 |
| Creative Suite Annotations | 17 | 1 | 2 | 1 | 2 | 0 |
| **Total** | **431** | **6** | **30** | **7** | **11+** | **15** |

Counts are based on extracted source paths, not ZIP names or product descriptions.

## Verified principal entry points

### Advanced Image

- `System/AdvancedImageServiceProvider.php`
- `System/Services/AdvancedFreepikService.php`
- `System/Services/AdvancedNovitaService.php`
- `System/Services/ClipDropService.php`
- `System/Services/FalAIService.php`
- `System/Services/NanoBananaService.php`
- `System/Services/OpenAIService.php`
- `database/migrations/2025_01_20_080747_advanced_image_migrations.php`
- `database/migrations/2025_01_20_080747_is_advanced_image_to_user_openai_table.php`

### AI Image Pro

- `System/AIImageProServiceProvider.php`
- `System/Jobs/GenerateAIImageJob.php`
- `System/Jobs/PollImageGenerationJob.php`
- `System/Models/AiImageProModel.php`
- `System/Models/AiImageProLikeModel.php`
- `System/Services/AIImageProService.php`
- `System/Services/RealtimeGenerationService.php`
- seven migrations covering records, publishing, likes, share tokens and dimensions

### AI Photoshoot

- `System/AIPhotoshootServiceProvider.php`
- `System/AIPhotoshootProviderInterface.php`
- `System/AIPhotoshootProviderFactory.php`
- `System/AIPhotoshootImageModelRegistry.php`
- `System/Services/AIPhotoshootFalService.php`
- three generation/polling jobs
- product, background and user-setting models
- five migrations

### Creative Suite

- `System/CreativeSuiteServiceProvider.php`
- `System/Models/CreativeSuiteDocument.php`
- `database/migrations/2024_05_08_163635_create_ext_creative_suite_documents_table.php`
- canvas/editor resources under `resources/`

### Creative Suite AI Template

- `System/CreativeSuiteAITemplateServiceProvider.php`
- `System/Jobs/GenerateTemplateJob.php`
- no independent migrations detected

### Creative Suite Annotations

- `System/CreativeSuiteAnnotationsServiceProvider.php`
- `System/Jobs/ProcessAnnotationEditJob.php`
- `System/Services/CreativeSuiteAnnotationsAIService.php`
- `System/Services/CreativeSuiteAnnotationsVisionService.php`
- no independent migrations detected

## Immediate verified findings

1. All six `extension.json` files contain a UTF-8 byte-order mark. Strict JSON decoders that do not accept BOM-prefixed JSON will reject them. The host loader must be checked before altering the files.
2. The two Creative Suite add-ons have no migrations, supporting the provisional view that they extend Creative Suite rather than own a separate persistence model.
3. AI Photoshoot already contains a provider interface, factory and model registry. This is a stronger conversion base than a prompt-only extension, but its actual isolation quality still requires namespace/config/table tracing.
4. Advanced Image contains at least six named provider services, making provider capability duplication and settings drift a likely audit focus.
5. AI Image Pro has the largest persistence surface and includes publishing, likes, sharing and image dimensions. It should not be treated as only an image generator.

## Next trace

Scan 2 will inspect every service provider and route/controller chain, then classify reachable, conditional, duplicated and orphaned components. No conversion implementation will begin until identifier and persistence collisions are mapped.
