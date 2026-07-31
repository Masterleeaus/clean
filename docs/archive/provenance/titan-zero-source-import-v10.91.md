> [!IMPORTANT]
> **Historical record — not current implementation guidance.** This document is retained for provenance because it describes an earlier branch, source version, import, or completed upgrade pass. Use `docs/README.md` and `docs/plans/CURRENT_UPGRADE_PLAN.md` for current guidance.

# Titan Zero Source Import

Imported source composition:

- MagicAI v10.91 host application at repository root
- WorkCore operational bounded domain under `app/Domains/WorkCore`
- Titan Zero Chatbot extension under `app/Extensions/TitanZeroChatbot`

Source archives used:

- `MagicAI-v10.91-WORKCORE-MERGED.zip`
- `Titan-Zero-Chatbot-PWA-PASS12-HOST-BOUNDARY-FIXED(1).zip`

Import validation confirmed the presence of:

- `artisan`
- `composer.json`
- `app/Domains/WorkCore/WorkCoreServiceProvider.php`
- `app/Extensions/TitanZeroChatbot/extension.json`

The repository must remain private while it contains licensed MagicAI source code.
