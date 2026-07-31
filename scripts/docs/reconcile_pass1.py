#!/usr/bin/env python3
"""First-pass documentation reconciliation for the Titan Zero repository.

This script is intentionally conservative:
- exact byte-for-byte duplicates are removed;
- source ZIPs are removed only after extraction inventories exist;
- branch-era and version-specific documents are retained under docs/archive;
- one current upgrade plan and one documentation entry point are promoted;
- doctrine/reference documents are preserved as reference material.
"""

from __future__ import annotations

import hashlib
import json
import re
import shutil
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
DOCS = ROOT / "docs"
BASELINE_SHA = "e565d7594e062c6705be9747bee0bd6081beb137"

HISTORICAL_BANNER = (
    "> [!IMPORTANT]\n"
    "> **Historical record — not current implementation guidance.** "
    "This document is retained for provenance because it describes an earlier branch, "
    "source version, import, or completed upgrade pass. Use `docs/README.md` and "
    "`docs/plans/CURRENT_UPGRADE_PLAN.md` for current guidance.\n\n"
)

CURRENT_PLAN_BANNER = (
    "> [!NOTE]\n"
    f"> **Current coordination baseline:** repository `main` at `{BASELINE_SHA}`. "
    "All agents preserve their old branches as evidence, but port only unique, verified "
    "deltas onto a fresh integration branch created from current `main`. Old branches are "
    "not merged wholesale.\n\n"
)


def ensure_parent(path: Path) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)


def move_file(source: str, destination: str, *, historical: bool = False) -> str:
    src = ROOT / source
    dst = ROOT / destination
    if not src.exists():
        return f"skip-missing: {source}"
    if dst.exists():
        raise RuntimeError(f"Destination already exists: {destination}")
    ensure_parent(dst)
    data = src.read_bytes()
    if historical and src.suffix.lower() in {".md", ".markdown"}:
        text = data.decode("utf-8", errors="replace")
        if "Historical record — not current implementation guidance" not in text:
            text = HISTORICAL_BANNER + text
        data = text.encode("utf-8")
    dst.write_bytes(data)
    src.unlink()
    return f"moved: {source} -> {destination}"


def promote_current_plan() -> str:
    src = ROOT / "TITAN-ZERO-UPGRADE-PLAN.md"
    dst = DOCS / "plans" / "CURRENT_UPGRADE_PLAN.md"
    if not src.exists():
        return "skip-missing: TITAN-ZERO-UPGRADE-PLAN.md"
    if dst.exists():
        raise RuntimeError("Current upgrade plan destination already exists")
    text = src.read_text(encoding="utf-8", errors="replace")
    text = re.sub(
        r"^# Titan Zero Repository Stabilisation and Upgrade Plan\s*",
        "# Titan Zero Current Upgrade Plan\n\n",
        text,
        count=1,
    )
    text = text.replace("Status: in progress.", "Status: active reconciliation and stabilisation.")
    text = text.replace(
        "1. Work only from isolated repair branches.",
        "1. Create one integration base from the latest verified `main`, then use isolated child branches.",
    )
    text = text.replace(
        "6. Record each repair pass in commits and pull requests rather than force-pushing `main`.",
        "6. Record each repair pass in focused commits and pull requests; never force-push or bulk-merge an obsolete branch into `main`.",
    )
    text = text.split("\n", 1)[0] + "\n\n" + CURRENT_PLAN_BANNER + text.split("\n", 1)[1]
    ensure_parent(dst)
    dst.write_text(text, encoding="utf-8")
    src.unlink()
    return "promoted: TITAN-ZERO-UPGRADE-PLAN.md -> docs/plans/CURRENT_UPGRADE_PLAN.md"


def move_reference_collections() -> list[str]:
    results: list[str] = []
    mapping = {
        DOCS / "inbox" / "archive-1": DOCS / "reference" / "titan-library" / "collection-1",
        DOCS / "inbox" / "archive-2": DOCS / "reference" / "titan-library" / "collection-2",
    }
    for src, dst in mapping.items():
        if src.exists():
            if dst.exists():
                raise RuntimeError(f"Reference destination already exists: {dst}")
            ensure_parent(dst)
            shutil.move(str(src), str(dst))
            results.append(f"moved-tree: {src.relative_to(ROOT)} -> {dst.relative_to(ROOT)}")
        else:
            results.append(f"skip-missing-tree: {src.relative_to(ROOT)}")
    inbox = DOCS / "inbox"
    if inbox.exists() and not any(inbox.iterdir()):
        inbox.rmdir()
    return results


def remove_exact_duplicates() -> list[str]:
    candidates: list[Path] = [
        DOCS / "reference/titan-library/collection-1/01-Ecosystem-Vision/Titan Zero Doctrine.pdf",
        DOCS / "reference/titan-library/collection-1/05-Engine-Platform/Worksuite_Module_Doctrine_v1.0_LOCKED.pdf",
        DOCS / "reference/titan-library/collection-2/13-Blueprints-Patterns/Titan Core Engine Map (Master Blueprint).pdf",
        DOCS / "reference/titan-library/collection-2/13-Blueprints-Patterns/Titan Zero architecture blueprint .pdf",
        DOCS / "reference/titan-library/collection-2/08-Communications-Channels/Titan Engine APIs & Contracts (Implementation Guide).pdf",
    ]
    foundation = DOCS / "reference/titan-library/collection-1/00-Foundation"
    if foundation.exists():
        candidates.extend(foundation.glob("*Database Doctrine & Schema Model.pdf"))

    removed: list[str] = []
    for path in candidates:
        if path.exists():
            removed.append(path.relative_to(ROOT).as_posix())
            path.unlink()
    return removed


def remove_extracted_source_archives() -> list[str]:
    inventory = DOCS / "inventory" / "archive_inventory.json"
    if not inventory.exists():
        raise RuntimeError("Archive inventory is missing; refusing to remove source ZIPs")
    report = json.loads(inventory.read_text(encoding="utf-8"))
    if len(report.get("archives", [])) != 2:
        raise RuntimeError("Archive inventory does not confirm two extracted archives")
    removed: list[str] = []
    for name in ("Archive.zip", "Archive 2.zip"):
        path = DOCS / name
        if path.exists():
            removed.append(path.relative_to(ROOT).as_posix())
            path.unlink()
    return removed


def sha256_file(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def title_for(path: Path) -> str:
    text_extensions = {".md", ".markdown", ".txt", ".rst", ".adoc", ".html", ".htm", ".csv", ".json", ".yaml", ".yml", ".xml"}
    if path.suffix.lower() not in text_extensions:
        return path.stem
    text = path.read_text(encoding="utf-8", errors="replace")[:16000]
    for line in text.splitlines():
        candidate = line.strip()
        if not candidate or candidate.startswith("> [!"):
            continue
        candidate = re.sub(r"^#{1,6}\s*", "", candidate).strip()
        if candidate:
            return candidate[:180]
    return path.stem


def classification(path: Path) -> str:
    rel = path.relative_to(ROOT).as_posix()
    if rel.startswith("docs/archive/"):
        return "historical"
    if rel.startswith("docs/reference/"):
        return "reference-only"
    if rel.startswith("docs/plans/") or rel.startswith("docs/architecture/") or rel.startswith("docs/governance/"):
        return "canonical"
    if rel.startswith("docs/audits/") or rel.startswith("docs/provenance/") or rel.startswith("docs/inventory/"):
        return "evidence"
    if rel in {"README.md", "AGENTS.md", "docs/README.md"}:
        return "canonical"
    return "review-required"


def build_catalogue() -> dict[str, object]:
    extensions = {".md", ".markdown", ".txt", ".rst", ".adoc", ".pdf", ".docx", ".html", ".htm", ".csv", ".json", ".yaml", ".yml", ".xml"}
    excluded_roots = (".git/", "vendor/", "node_modules/", "storage/", "public/build/", ".worktrees/")
    documents: list[dict[str, object]] = []
    for path in sorted(ROOT.rglob("*")):
        if not path.is_file() or path.suffix.lower() not in extensions:
            continue
        rel = path.relative_to(ROOT).as_posix()
        if rel.startswith(excluded_roots):
            continue
        if not (rel.startswith("docs/") or "/" not in rel):
            continue
        documents.append({
            "path": rel,
            "title": title_for(path),
            "extension": path.suffix.lower(),
            "size": path.stat().st_size,
            "sha256": sha256_file(path),
            "classification": classification(path),
        })

    groups: dict[str, list[str]] = defaultdict(list)
    for item in documents:
        groups[str(item["sha256"])].append(str(item["path"]))
    duplicates = [
        {"sha256": digest, "paths": paths}
        for digest, paths in sorted(groups.items())
        if len(paths) > 1
    ]
    report: dict[str, object] = {
        "generated_from_main_sha": BASELINE_SHA,
        "document_count": len(documents),
        "duplicate_group_count": len(duplicates),
        "documents": documents,
        "exact_duplicate_groups": duplicates,
    }
    path = DOCS / "inventory" / "documentation_catalogue.json"
    ensure_parent(path)
    path.write_text(json.dumps(report, indent=2, sort_keys=True) + "\n", encoding="utf-8")
    return report


def write_reference_readme() -> None:
    inventory_path = DOCS / "inventory" / "archive_inventory.json"
    inventory = json.loads(inventory_path.read_text(encoding="utf-8"))
    archive_lines = []
    for entry in inventory.get("archives", []):
        archive_lines.append(
            f"- **{entry['label']}** — source `{entry['source']}`, SHA-256 `{entry['sha256']}`, "
            f"{entry['files_extracted']} extracted files."
        )
    path = DOCS / "reference" / "titan-library" / "README.md"
    ensure_parent(path)
    path.write_text(
        "# Titan Reference Library\n\n"
        "> [!CAUTION]\n"
        "> These collections are preserved design, doctrine and product-reference material. "
        "They are not automatically authoritative over the current codebase. Implementation decisions "
        "must be reconciled against current architecture, tests and source.\n\n"
        "## Collections\n\n"
        + "\n".join(archive_lines)
        + "\n\n## Use rules\n\n"
        "1. Treat documents as reference-only until explicitly promoted.\n"
        "2. Prefer Markdown sources for editing; retain PDF/DOCX originals as evidence.\n"
        "3. Do not implement conflicting authority, tenancy or data ownership from an older document.\n"
        "4. Record any promoted doctrine in a current architecture decision or governance document.\n",
        encoding="utf-8",
    )


def write_policy() -> None:
    path = DOCS / "governance" / "DOCUMENTATION_POLICY.md"
    ensure_parent(path)
    path.write_text(
        "# Titan Zero Documentation Policy\n\n"
        "## Authority order\n\n"
        "When documents disagree, use this order:\n\n"
        "1. Current tested source and accepted architecture tests.\n"
        "2. Current documents listed in `docs/README.md`.\n"
        "3. Accepted architecture decisions and security/tenancy rules.\n"
        "4. Current audits and provenance records.\n"
        "5. Reference-library doctrine and designs.\n"
        "6. Historical branch plans and status reports.\n\n"
        "## Dispositions\n\n"
        "Every reviewed document receives one disposition: `canonical`, `merge-into`, "
        "`historical`, `superseded-delete`, or `reference-only`.\n\n"
        "## Deletion rule\n\n"
        "Delete a document only when it is an exact duplicate, contains no unique evidence, or its "
        "unique content has been merged into a named canonical document. Git history remains the final "
        "recovery record.\n\n"
        "## Agent coordination rule\n\n"
        "Agents preserve their old branches, but port only unique, verified deltas onto one fresh "
        "integration base built from current `main`. Documentation follows the same rule: preserve "
        "history, promote only current verified guidance, and never combine every old plan into one "
        "unreviewed document.\n",
        encoding="utf-8",
    )


def write_docs_index(report: dict[str, object]) -> None:
    path = DOCS / "README.md"
    path.write_text(
        "# Titan Zero Documentation\n\n"
        "This directory separates current engineering guidance from evidence, historical records and "
        "the extracted Titan reference library.\n\n"
        "## Start here\n\n"
        "- [Current upgrade plan](plans/CURRENT_UPGRADE_PLAN.md)\n"
        "- [Authority map](architecture/TITAN_ZERO_AUTHORITY_MAP.md)\n"
        "- [Documentation policy](governance/DOCUMENTATION_POLICY.md)\n"
        "- [MagicAI v11.00 import audit](audits/magicai-v1100-source-inventory.md)\n"
        "- [Documentation reconciliation status](DOCUMENTATION_RECONCILIATION_STATUS.md)\n\n"
        "## Directory guide\n\n"
        "| Directory | Purpose | Authority |\n"
        "|---|---|---|\n"
        "| `architecture/` | Current system boundaries and ownership | Canonical when listed above |\n"
        "| `plans/` | Current coordinated implementation plans | Current guidance |\n"
        "| `governance/` | Documentation, security and engineering rules | Canonical policy |\n"
        "| `audits/` | Evidence-based findings and verification | Evidence |\n"
        "| `provenance/` | Source origins, checksums and import decisions | Evidence |\n"
        "| `inventory/` | Generated documentation catalogues and dispositions | Generated evidence |\n"
        "| `reference/titan-library/` | Extracted doctrine, blueprints and product concepts | Reference-only |\n"
        "| `archive/` | Superseded plans, branch reports and old setup instructions | Historical only |\n\n"
        f"## Current reconciliation count\n\n- Catalogued documents after Pass 1: **{report['document_count']}**\n"
        f"- Remaining exact duplicate groups: **{report['duplicate_group_count']}**\n",
        encoding="utf-8",
    )


def write_root_readme() -> None:
    path = ROOT / "README.md"
    path.write_text(
        "# Titan Zero Clean Integration Workspace\n\n"
        "Private integration repository for the MagicAI host, WorkCore operational domain, Titan Zero "
        "Chatbot/PWA, Interaction and Wizard runtimes, five-tier intelligence, extensions and supporting "
        "services.\n\n"
        "## Current working rule\n\n"
        "`main` is the source baseline. Agents preserve old branches as evidence, but port only unique, "
        "verified deltas onto a fresh integration branch built from current `main`. Old branches are not "
        "merged wholesale.\n\n"
        "## Architecture boundaries\n\n"
        "- **MagicAI host:** authentication, users, tenancy, billing and application shell.\n"
        "- **WorkCore:** sole authority for operational business records and mutations.\n"
        "- **Titan Zero:** intent, orchestration, governance and delegation.\n"
        "- **Interaction Engine:** governed interaction and wizard execution.\n"
        "- **Chatbot/PWA:** user experience, device storage, offline state and synchronisation.\n"
        "- **Titan Vault:** credentials and protected configuration.\n\n"
        "## Documentation\n\n"
        "Begin with [`docs/README.md`](docs/README.md). Historical plans and source-specific reports are "
        "retained under `docs/archive/`; extracted doctrine and blueprints are under "
        "`docs/reference/titan-library/`.\n\n"
        "> This repository contains licensed/private source and must remain private.\n",
        encoding="utf-8",
    )


def write_status(report: dict[str, object], actions: list[str], removed_duplicates: list[str], removed_archives: list[str]) -> None:
    path = DOCS / "DOCUMENTATION_RECONCILIATION_STATUS.md"
    lines = [
        "# Documentation Reconciliation Status",
        "",
        "## Pass 1 — completed on reconciliation branch",
        "",
        f"- Baseline main SHA: `{BASELINE_SHA}`",
        "- Both uploaded archives were safety-checked and extracted.",
        "- Extracted collections were moved from `docs/inbox/` to `docs/reference/titan-library/`.",
        f"- Exact duplicate files removed: **{len(removed_duplicates)}**.",
        f"- Source ZIPs removed after inventory verification: **{len(removed_archives)}**.",
        "- Branch-era and v10.91-specific documents were retained under `docs/archive/` with historical banners.",
        "- One current upgrade plan was promoted to `docs/plans/CURRENT_UPGRADE_PLAN.md`.",
        f"- Documents catalogued after Pass 1: **{report['document_count']}**.",
        f"- Remaining exact duplicate groups: **{report['duplicate_group_count']}**.",
        "",
        "## Removed exact duplicates",
        "",
    ]
    lines.extend([f"- `{item}`" for item in removed_duplicates] or ["- None"])
    lines.extend(["", "## Historical moves", ""])
    lines.extend([f"- {item}" for item in actions])
    lines.extend([
        "",
        "## Next pass",
        "",
        "1. Review doctrine and architecture documents by subject cluster.",
        "2. Promote current architecture decisions into Markdown canonical documents.",
        "3. Merge unique information from overlapping plans before deleting any non-identical document.",
        "4. Repair internal links after the remaining plan and audit consolidation.",
        "5. Review duplicate runtime documentation under `app/Extensions/Chatbot` and `app/Extensions/TitanZeroChatbot` separately from this repository-level documentation pass.",
        "",
    ])
    path.write_text("\n".join(lines), encoding="utf-8")


def main() -> None:
    actions: list[str] = []
    actions.extend(move_reference_collections())

    historical_moves = {
        "AGENT2-PASS1-STATUS.md": "docs/archive/status/2026-07/agent2-pass1-status.md",
        "BRANCH_PREPARATION.md": "docs/archive/status/2026-07/branch-preparation.md",
        "BRANCH_PREPARATION_STATUS.md": "docs/archive/status/2026-07/branch-preparation-status.md",
        "PASS1_STATUS.md": "docs/archive/status/2026-07/titan-train-pass1-status.md",
        "PASS2_STATUS.md": "docs/archive/status/2026-07/titan-train-pass2-status.md",
        "PASS3_STATUS.md": "docs/archive/status/2026-07/titan-train-pass3-status.md",
        "TITAN_MONEY_TITAN_PAY_BRANCH_STATUS.md": "docs/archive/status/2026-07/titan-money-titan-pay-branch-status.md",
        "TITAN_TRAIN_LMS_BRANCH.md": "docs/archive/status/2026-07/titan-train-lms-branch.md",
        "UPGRADE_STATUS.md": "docs/archive/status/2026-07/pwa-upgrade-status.md",
        "WORKSPACE.md": "docs/archive/status/2026-07/titan-zero-development-workspace.md",
        "AGENT2-PWA-OFFLINE-UPGRADE-PLAN.md": "docs/archive/plans/2026-07/agent2-pwa-offline-upgrade-plan.md",
        "CREATIVE-EXTENSIONS-UPGRADE-PLAN.md": "docs/archive/plans/2026-07/creative-extensions-upgrade-plan.md",
        "EXTENSION_PLATFORM_UPGRADE_PLAN.md": "docs/archive/plans/2026-07/extension-platform-upgrade-plan.md",
        "MULTI_PASS_UPGRADE_PLAN.md": "docs/archive/plans/2026-07/interaction-wizard-multi-pass-upgrade-plan.md",
        "TITAN_MONEY_TITAN_PAY_CHAT_UPGRADE_PLAN.md": "docs/archive/plans/2026-07/titan-money-titan-pay-chat-upgrade-plan.md",
        "TITAN_TRAIN_LMS_UPGRADE_PLAN.md": "docs/archive/plans/2026-07/titan-train-lms-upgrade-plan.md",
        "TITAN_ZERO_CHATBOT_PWA_UPGRADE_PLAN.md": "docs/archive/plans/2026-07/titan-zero-chatbot-pwa-upgrade-plan.md",
        "TITAN_ZERO_UPGRADE_PLAN.md": "docs/archive/plans/2026-07/titan-zero-integrated-platform-upgrade-plan-v10.91.md",
        "TITAN_ZERO_WIZARD_UPGRADE_PLAN.md": "docs/archive/plans/2026-07/titan-zero-wizard-upgrade-plan.md",
        "DEV-SETUP.md": "docs/archive/setup/magicai-v10.91-development-setup.md",
        "DEV-PACKAGE-MANIFEST.txt": "docs/archive/provenance/magicai-v10.91-dev-package-manifest.txt",
        "SOURCE_BASELINE.md": "docs/archive/provenance/interaction-wizard-source-baseline-v10.91.md",
        "SOURCE_IMPORT.md": "docs/archive/provenance/titan-zero-source-import-v10.91.md",
        "SOURCE_PROVENANCE.md": "docs/archive/provenance/interaction-engine-source-provenance-v10.91.md",
        "VERIFICATION_REPORT.md": "docs/archive/reports/2026-07/interaction-engine-merge-report.md",
        "SUPERSEDED_FILES.md": "docs/archive/reports/2026-07/superseded-files-note.md",
        "REJECTED_FILES.md": "docs/archive/reports/2026-07/rejected-files-note.md",
        "docs/WORKCORE-MERGE-RESULT.md": "docs/archive/reports/2026-07/workcore-merge-result-v10.91.md",
        "docs/imports/SOURCE_IMPORT.md": "docs/archive/provenance/source-import-note-v10.91.md",
    }
    for source, destination in historical_moves.items():
        actions.append(move_file(source, destination, historical=True))

    current_provenance_moves = {
        "SOURCE_PROVENANCE_TITAN_MONEY_TITAN_PAY.md": "docs/provenance/titan-money-titan-pay.md",
        "SOURCE_IMPORT_TITAN_MONEY_TITAN_PAY.json": "docs/provenance/titan-money-titan-pay.json",
    }
    for source, destination in current_provenance_moves.items():
        actions.append(move_file(source, destination, historical=False))

    actions.append(promote_current_plan())
    removed_duplicates = remove_exact_duplicates()
    removed_archives = remove_extracted_source_archives()

    write_reference_readme()
    write_policy()
    write_root_readme()
    report = build_catalogue()
    write_docs_index(report)
    report = build_catalogue()
    write_status(report, actions, removed_duplicates, removed_archives)
    report = build_catalogue()

    dispositions = DOCS / "inventory" / "PASS1_DISPOSITIONS.md"
    dispositions.write_text(
        "# Documentation Pass 1 Dispositions\n\n"
        "## Canonical\n\n"
        "- `README.md`\n"
        "- `AGENTS.md`\n"
        "- `docs/README.md`\n"
        "- `docs/plans/CURRENT_UPGRADE_PLAN.md`\n"
        "- `docs/architecture/TITAN_ZERO_AUTHORITY_MAP.md`\n"
        "- `docs/governance/DOCUMENTATION_POLICY.md`\n\n"
        "## Evidence\n\n"
        "- `docs/audits/`\n"
        "- `docs/provenance/`\n"
        "- `docs/inventory/`\n\n"
        "## Reference-only\n\n"
        "- `docs/reference/titan-library/`\n\n"
        "## Historical\n\n"
        "- `docs/archive/`\n\n"
        "## Deleted\n\n"
        "- Two uploaded ZIP files after verified extraction and inventory.\n"
        f"- {len(removed_duplicates)} exact duplicate documents; surviving copies remain in the most appropriate subject folder.\n",
        encoding="utf-8",
    )

    print(json.dumps({
        "actions": actions,
        "removed_duplicates": removed_duplicates,
        "removed_archives": removed_archives,
        "document_count": report["document_count"],
        "remaining_duplicate_groups": report["duplicate_group_count"],
    }, indent=2))


if __name__ == "__main__":
    main()
