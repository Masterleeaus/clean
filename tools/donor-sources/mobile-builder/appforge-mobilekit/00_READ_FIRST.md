# Titan Mobile App Builder Donor Code Extraction v2

This archive contains selected donor code from the two exact source ZIPs supplied in the active conversation.

## Source archives

- AppForge: `appforge-aipowered-nocode-mobile-app-builder-saas-platform.zip`
  - SHA-256: `ffe0e0f3119825dea1bb1022cb0afc8171cbe7e30d59847598da43e56d2ad55a`
- MobileKit: `themeforest-mobilekit-bootstrap-4-based-mobile-ui-kit-template.zip`
  - SHA-256: `a2f9bda06a35a2d82217692bdff736a8b901cef795422fc98f5816f667c6a736`

## Folder model

- `01_APPFORGE/reuse_candidates/`
  - React builder, preview, branding, device, asset, build-status and UI primitives.
- `01_APPFORGE/adapt_required/`
  - Useful patterns still coupled to Supabase, donor AI, storage, or provider authority.
- `01_APPFORGE/build_and_dependency_reference/`
  - Original package/build configuration for reference only.
- `02_MOBILEKIT/reuse_candidates/design_system/`
  - Mobile-first Sass components, layout patterns and supporting vendor references.
- `02_MOBILEKIT/reference_screens/`
  - HTML screen and component examples to translate into governed Titan components.
- `02_MOBILEKIT/adapt_required/pwa_runtime/`
  - Legacy PWA and DOM runtime patterns requiring security and architecture replacement.
- `02_MOBILEKIT/third_party_licenses/`
  - Third-party license texts shipped with MobileKit.
- `03_NO_CODE_MOBILE_BUILDER_SOURCE_NOT_YET_ATTACHED/`
  - Reserved extraction structure; no code has been invented or claimed.

## Important

This is a donor-code archive, not an installable application.

Do not merge folders wholesale. Every file remains subject to:

1. WorkCore/MagicAI company and permission authority.
2. Titan Interaction Engine validation and confirmation.
3. Titan Vault for secrets.
4. Component-registry and schema validation.
5. Current dependency and licensing review.

AppForge's package metadata says ISC, but the archive is a commercial CodeCanyon product and contains no clear repository-level licence file. Verify purchased licence terms before commercial reuse.

MobileKit is a ThemeForest product. Its bundled third-party licences do not replace the ThemeForest item licence. Verify purchased licence terms before copying implementation code into a commercial product.
