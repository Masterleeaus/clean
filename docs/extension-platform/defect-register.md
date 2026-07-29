# Extension Defect Register

## Confirmed defects

| ID | Extension | Severity | Finding | Required action |
|---|---|---:|---|---|
| EXT-001 | Midjourney | Critical | The webhook controller immediately returns HTTP 200 before all processing logic, making the implementation unreachable. | Remove the early return and test callback processing. |
| EXT-002 | Midjourney | High | The webhook tests for an empty image URL and then attempts to download that empty URL; the condition is reversed. | Validate a non-empty URL before download. |
| EXT-003 | Midjourney | Critical | PiAPI generation uses a hard-coded request-catcher webhook endpoint and literal secret. | Generate configured signed callback URLs and rotate/remove the exposed placeholder secret. |
| EXT-004 | SeeDream V4 | Critical | The generator tab, body, and script are entirely commented out. | Restore functional views using SeeDream-specific identifiers and endpoints. |
| EXT-005 | SeeDream V4 | High | Commented source still references Nano Banana generator names, IDs, model values, and functions. | Replace copied identifiers and add browser tests preventing cross-extension collisions. |
| EXT-006 | Nano Banana / SeeDream V4 | High | Both providers register the generic `generator/webhook/fal-ai` route and similarly named controllers. | Introduce provider-qualified webhook routes or one shared dispatcher. |
| EXT-007 | OpenAI Realtime Chat | High | A sample-data migration is included and its `down()` method is empty. | Replace it with intentional schema or remove it; add reversible migrations. |
| EXT-008 | OpenRouter | Medium | `System/Moldes` is misspelled and contains only packaging placeholders. | Remove from transformed code or rename if a model namespace is intended. |
| EXT-009 | Nano Banana | Medium | Extension uninstall lifecycle is declared but not implemented. | Implement cleanup or explicitly mark persistent data policy. |
| EXT-010 | MultiModel / SeeDream V4 | Low | `.DS_Store` packaging artifacts are committed. | Exclude via repository ignore rules and remove from transformed source. |

## Structural risks

- Provider credentials appear to use global settings without clear tenant/user/device scoping.
- Provider-specific controllers and Blade scripts duplicate transport, loading, polling, error, and rendering behavior.
- Webhook verification, replay protection, idempotency, and event audit trails are not consistently visible.
- Image download code requires explicit SSRF, MIME, size, timeout, redirect, and storage-policy protections.
- Model catalogues are partly hard-coded, creating drift against provider capabilities.
- Model Council is concentrated in a very large service and should be split before major feature expansion.
- Route, settings-key, controller-name, and DOM-ID collisions are likely when all extensions are enabled together.
