# Reference Source Register

This directory records external and uploaded source packages used during the Titan Zero upgrade.

Do not commit large archives here without a clear implementation need. Prefer manifests, checksums, licence notes and selected extracted files.

## Supplied source register

| Source | Intended use | Admission status |
|---|---|---|
| MagicAI-v10.91-WORKCORE-MERGED.zip | Canonical Laravel host and WorkCore base | Pending inventory |
| Titan-Zero-Chatbot-PWA-PASS12-HOST-BOUNDARY-FIXED(1).zip | Canonical PWA and device runtime base | Pending inventory |
| TitanZero-Extension-SDK-v2.0.0(1).zip | Canonical extension contract candidate | Pending audit |
| Base App System Extensions.zip | Host and operational extension candidates | Pending classification |
| AI System Extensions.zip | AI workforce extension candidates | Pending classification |
| Marketing & Creative Extensions.zip | Marketing and creative extension candidates | Pending classification |
| Modules for Titan BOS.zip | Titan BOS module candidates | Pending classification |
| aipowered-nocode-mobile-app-builder-saas-platform.zip | Sprout builder, component registry, preview and native bridge donor | Donor only; pending extraction audit |
| mobilekit-bootstrap-4-based-mobile-ui-kit-template.zip | Mobile interaction and component donor | Donor only; pending modernisation audit |
| online-app-builder-from-website.zip | Native packaging, white-label and build-pipeline donor | Donor only; pending extraction audit |
| WorkCore Technical Architecture Specification.txt | Canonical architecture reference | Authoritative design reference |
| Workcore.txt | WorkCore product/domain reference | Pending reconciliation |
| Extension System.txt | Extension platform reference | Pending reconciliation |

## Required provenance record

For each admitted source or file, create a record containing:

- source archive
- source path
- checksum
- licence
- selected code or concept
- target path
- modifications required
- dependencies
- tests
- security review status

## Rules

1. Never nest a donor application inside the Titan product.
2. Extract only code or patterns that serve the canonical architecture.
3. Preserve licence and copyright notices.
4. Prefer source over compiled assets.
5. Do not import vendor, node_modules, caches, logs, uploads or secrets.
6. Reconcile duplicate namespaces, routes, migrations and tables before admission.
