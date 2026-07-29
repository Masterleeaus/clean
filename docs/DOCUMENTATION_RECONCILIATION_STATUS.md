# Documentation Reconciliation Status

## Pass 1 — completed on reconciliation branch

- Baseline main SHA: `e565d7594e062c6705be9747bee0bd6081beb137`
- Both uploaded archives were safety-checked and extracted.
- Extracted collections were moved from `docs/inbox/` to `docs/reference/titan-library/`.
- Exact duplicate files removed: **6**.
- Source ZIPs removed after inventory verification: **2**.
- Branch-era and v10.91-specific documents were retained under `docs/archive/` with historical banners.
- One current upgrade plan was promoted to `docs/plans/CURRENT_UPGRADE_PLAN.md`.
- Documents catalogued after Pass 1: **377**.
- Remaining exact duplicate groups: **0**.

## Removed exact duplicates

- `docs/reference/titan-library/collection-1/01-Ecosystem-Vision/Titan Zero Doctrine.pdf`
- `docs/reference/titan-library/collection-1/05-Engine-Platform/Worksuite_Module_Doctrine_v1.0_LOCKED.pdf`
- `docs/reference/titan-library/collection-2/13-Blueprints-Patterns/Titan Core Engine Map (Master Blueprint).pdf`
- `docs/reference/titan-library/collection-2/13-Blueprints-Patterns/Titan Zero architecture blueprint .pdf`
- `docs/reference/titan-library/collection-2/08-Communications-Channels/Titan Engine APIs & Contracts (Implementation Guide).pdf`
- `docs/reference/titan-library/collection-1/00-Foundation/TitanZero ╬ôC╠ºo╠ê Database Doctrine & Schema Model.pdf`

## Historical moves

- moved-tree: docs/inbox/archive-1 -> docs/reference/titan-library/collection-1
- moved-tree: docs/inbox/archive-2 -> docs/reference/titan-library/collection-2
- moved: AGENT2-PASS1-STATUS.md -> docs/archive/status/2026-07/agent2-pass1-status.md
- moved: BRANCH_PREPARATION.md -> docs/archive/status/2026-07/branch-preparation.md
- moved: BRANCH_PREPARATION_STATUS.md -> docs/archive/status/2026-07/branch-preparation-status.md
- moved: PASS1_STATUS.md -> docs/archive/status/2026-07/titan-train-pass1-status.md
- moved: PASS2_STATUS.md -> docs/archive/status/2026-07/titan-train-pass2-status.md
- moved: PASS3_STATUS.md -> docs/archive/status/2026-07/titan-train-pass3-status.md
- moved: TITAN_MONEY_TITAN_PAY_BRANCH_STATUS.md -> docs/archive/status/2026-07/titan-money-titan-pay-branch-status.md
- moved: TITAN_TRAIN_LMS_BRANCH.md -> docs/archive/status/2026-07/titan-train-lms-branch.md
- moved: UPGRADE_STATUS.md -> docs/archive/status/2026-07/pwa-upgrade-status.md
- moved: WORKSPACE.md -> docs/archive/status/2026-07/titan-zero-development-workspace.md
- moved: AGENT2-PWA-OFFLINE-UPGRADE-PLAN.md -> docs/archive/plans/2026-07/agent2-pwa-offline-upgrade-plan.md
- moved: CREATIVE-EXTENSIONS-UPGRADE-PLAN.md -> docs/archive/plans/2026-07/creative-extensions-upgrade-plan.md
- moved: EXTENSION_PLATFORM_UPGRADE_PLAN.md -> docs/archive/plans/2026-07/extension-platform-upgrade-plan.md
- moved: MULTI_PASS_UPGRADE_PLAN.md -> docs/archive/plans/2026-07/interaction-wizard-multi-pass-upgrade-plan.md
- moved: TITAN_MONEY_TITAN_PAY_CHAT_UPGRADE_PLAN.md -> docs/archive/plans/2026-07/titan-money-titan-pay-chat-upgrade-plan.md
- moved: TITAN_TRAIN_LMS_UPGRADE_PLAN.md -> docs/archive/plans/2026-07/titan-train-lms-upgrade-plan.md
- moved: TITAN_ZERO_CHATBOT_PWA_UPGRADE_PLAN.md -> docs/archive/plans/2026-07/titan-zero-chatbot-pwa-upgrade-plan.md
- moved: TITAN_ZERO_UPGRADE_PLAN.md -> docs/archive/plans/2026-07/titan-zero-integrated-platform-upgrade-plan-v10.91.md
- moved: TITAN_ZERO_WIZARD_UPGRADE_PLAN.md -> docs/archive/plans/2026-07/titan-zero-wizard-upgrade-plan.md
- moved: DEV-SETUP.md -> docs/archive/setup/magicai-v10.91-development-setup.md
- moved: DEV-PACKAGE-MANIFEST.txt -> docs/archive/provenance/magicai-v10.91-dev-package-manifest.txt
- moved: SOURCE_BASELINE.md -> docs/archive/provenance/interaction-wizard-source-baseline-v10.91.md
- moved: SOURCE_IMPORT.md -> docs/archive/provenance/titan-zero-source-import-v10.91.md
- moved: SOURCE_PROVENANCE.md -> docs/archive/provenance/interaction-engine-source-provenance-v10.91.md
- moved: VERIFICATION_REPORT.md -> docs/archive/reports/2026-07/interaction-engine-merge-report.md
- moved: SUPERSEDED_FILES.md -> docs/archive/reports/2026-07/superseded-files-note.md
- moved: REJECTED_FILES.md -> docs/archive/reports/2026-07/rejected-files-note.md
- moved: docs/WORKCORE-MERGE-RESULT.md -> docs/archive/reports/2026-07/workcore-merge-result-v10.91.md
- moved: docs/imports/SOURCE_IMPORT.md -> docs/archive/provenance/source-import-note-v10.91.md
- moved: SOURCE_PROVENANCE_TITAN_MONEY_TITAN_PAY.md -> docs/provenance/titan-money-titan-pay.md
- moved: SOURCE_IMPORT_TITAN_MONEY_TITAN_PAY.json -> docs/provenance/titan-money-titan-pay.json
- promoted: TITAN-ZERO-UPGRADE-PLAN.md -> docs/plans/CURRENT_UPGRADE_PLAN.md

## Next pass

1. Review doctrine and architecture documents by subject cluster.
2. Promote current architecture decisions into Markdown canonical documents.
3. Merge unique information from overlapping plans before deleting any non-identical document.
4. Repair internal links after the remaining plan and audit consolidation.
5. Review duplicate runtime documentation under `app/Extensions/Chatbot` and `app/Extensions/TitanZeroChatbot` separately from this repository-level documentation pass.
