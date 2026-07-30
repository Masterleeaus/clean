#!/usr/bin/env python3
"""Move repository-root documentation into the governed docs tree.

The root keeps README.md, AGENTS.md, recognised legal files and machine/build
configuration. Documentation is classified by purpose, moved without changing
its substantive content, and recorded in machine- and human-readable reports.
"""

from __future__ import annotations

import hashlib
import json
import re
import shutil
from dataclasses import dataclass, asdict
from datetime import date
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
DOCS = ROOT / "docs"
TODAY = date.today().isoformat()
ARCHIVE_MONTH = TODAY[:7]

ROOT_ENTRYPOINTS = {"README.md", "AGENTS.md"}
LEGAL_PREFIXES = ("LICENSE", "LICENCE", "COPYING", "NOTICE")
DOC_EXTENSIONS = {".md", ".markdown", ".txt", ".pdf", ".doc", ".docx", ".odt", ".rtf"}
TEXT_SCAN_EXTENSIONS = {".md", ".markdown", ".txt", ".json", ".yml", ".yaml", ".php", ".js", ".ts", ".tsx", ".jsx", ".xml"}
RUNTIME_ROOT_FILES = {
    "composer.json", "composer.lock", "package.json", "package-lock.json", "pnpm-lock.yaml",
    "yarn.lock", "phpunit.xml", "phpunit.xml.dist", "vite.config.js", "vite.config.ts",
    "tailwind.config.js", "postcss.config.js", "docker-compose.yml", "docker-compose.yaml",
    "manifest.json", "mix-manifest.json", "tsconfig.json", "jsconfig.json", "version.txt",
}
JSON_DOC_MARKERS = (
    "PROVENANCE", "SOURCE_IMPORT", "SOURCE-IMPORT", "SOURCE_ARCHIVE", "SOURCE-ARCHIVE",
    "IMPORT_MANIFEST", "IMPORT-MANIFEST", "DOCUMENTATION", "INVENTORY", "CATALOGUE",
    "CATALOG", "AUDIT", "VERIFICATION", "REPORT", "DISPOSITION", "CHECKSUM", "SHA256",
)


@dataclass
class MoveRecord:
    source: str
    destination: str
    category: str
    size: int
    sha256: str
    historical_banner_added: bool


def sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def is_legal(path: Path) -> bool:
    upper = path.name.upper()
    return any(upper.startswith(prefix) for prefix in LEGAL_PREFIXES)


def is_json_document(path: Path) -> bool:
    if path.suffix.lower() != ".json" or path.name in RUNTIME_ROOT_FILES:
        return False
    upper = path.name.upper()
    return any(marker in upper for marker in JSON_DOC_MARKERS)


def is_root_document(path: Path) -> bool:
    if not path.is_file() or path.name.startswith("."):
        return False
    if path.name in ROOT_ENTRYPOINTS or path.name in RUNTIME_ROOT_FILES or is_legal(path):
        return False
    return path.suffix.lower() in DOC_EXTENSIONS or is_json_document(path)


def slugged_name(name: str) -> str:
    stem = Path(name).stem
    suffix = Path(name).suffix.lower()
    slug = re.sub(r"[^A-Za-z0-9._-]+", "-", stem).strip("-_").lower()
    return f"{slug or 'document'}{suffix}"


def classify(path: Path) -> tuple[str, Path, bool]:
    upper = path.name.upper()
    name = slugged_name(path.name)

    if path.name in {"CONTRIBUTING.md", "CODE_OF_CONDUCT.md", "SECURITY.md", "SUPPORT.md"}:
        return "governance", DOCS / "governance" / name, False
    if "ARCHITECTURE" in upper or "AUTHORITY" in upper or "BOUNDARY" in upper or "CONTRACT" in upper:
        return "architecture", DOCS / "architecture" / "root-imported" / name, False
    if "CHANGELOG" in upper or "RELEASE" in upper:
        return "release", DOCS / "releases" / name, False
    if any(token in upper for token in ("PROVENANCE", "SOURCE_IMPORT", "SOURCE-IMPORT", "SOURCE_ARCHIVE", "SOURCE-ARCHIVE", "IMPORT_MANIFEST", "IMPORT-MANIFEST", "BASELINE")):
        return "provenance", DOCS / "provenance" / "root-imported" / name, False
    if any(token in upper for token in ("INVENTORY", "CATALOGUE", "CATALOG", "CHECKSUM", "SHA256", "FILE-LIST", "FILES_LIST")):
        return "inventory", DOCS / "inventory" / "root-imported" / name, False
    if any(token in upper for token in ("AUDIT", "VERIFICATION", "SCAN", "ASSESSMENT", "REPORT")):
        return "audit", DOCS / "audits" / "root-imported" / name, False
    if any(token in upper for token in ("SETUP", "INSTALL", "DEPLOY", "RUNBOOK", "OPERATIONS_GUIDE", "DEVELOPMENT_GUIDE")):
        return "setup", DOCS / "setup" / "root-imported" / name, False
    if any(token in upper for token in ("PLAN", "ROADMAP", "UPGRADE", "IMPLEMENTATION")):
        return "historical-plan", DOCS / "archive" / "plans" / ARCHIVE_MONTH / name, True
    if any(token in upper for token in ("STATUS", "BRANCH", "PASS", "CHECKPOINT", "COMPLETE", "COMPLETION")):
        return "historical-status", DOCS / "archive" / "status" / ARCHIVE_MONTH / name, True
    if any(token in upper for token in ("REJECTED", "SUPERSEDED", "LEGACY", "OLD")):
        return "historical-report", DOCS / "archive" / "reports" / ARCHIVE_MONTH / name, True

    return "historical-root-document", DOCS / "archive" / "root-documents" / ARCHIVE_MONTH / name, True


def unique_destination(destination: Path, source: Path) -> Path:
    if not destination.exists():
        return destination
    if destination.is_file() and sha256(destination) == sha256(source):
        return destination
    stem, suffix = destination.stem, destination.suffix
    counter = 2
    while True:
        candidate = destination.with_name(f"{stem}-root-{counter}{suffix}")
        if not candidate.exists():
            return candidate
        counter += 1


def add_historical_banner(path: Path, original_name: str) -> bool:
    if path.suffix.lower() not in {".md", ".markdown"}:
        return False
    text = path.read_text(encoding="utf-8", errors="replace")
    if "HISTORICAL DOCUMENT" in text[:500]:
        return False
    banner = (
        "> [!WARNING]\n"
        "> **HISTORICAL DOCUMENT.** This file was moved from the repository root during documentation reconciliation. "
        "It is retained for provenance and must not override current canonical documents listed in `docs/README.md`.\n\n"
        f"_Original root path: `{original_name}`._\n\n"
    )
    path.write_text(banner + text, encoding="utf-8")
    return True


def move_documents() -> list[MoveRecord]:
    records: list[MoveRecord] = []
    candidates = sorted((path for path in ROOT.iterdir() if is_root_document(path)), key=lambda p: p.name.lower())

    for source in candidates:
        category, destination, historical = classify(source)
        destination = unique_destination(destination, source)
        destination.parent.mkdir(parents=True, exist_ok=True)
        original_hash = sha256(source)
        original_size = source.stat().st_size

        if destination.exists() and sha256(destination) == original_hash:
            source.unlink()
            banner_added = False
        else:
            shutil.move(str(source), str(destination))
            banner_added = add_historical_banner(destination, source.name) if historical else False

        records.append(MoveRecord(
            source=source.name,
            destination=destination.relative_to(ROOT).as_posix(),
            category=category,
            size=original_size,
            sha256=original_hash,
            historical_banner_added=banner_added,
        ))

    return records


def scan_references(records: list[MoveRecord]) -> dict[str, list[str]]:
    references: dict[str, list[str]] = {record.source: [] for record in records}
    ignored_parts = {".git", "vendor", "node_modules", "storage", "bootstrap", "cache"}
    report_paths = {
        "docs/inventory/ROOT_DOCUMENT_CONSOLIDATION.md",
        "docs/inventory/root_document_moves.json",
        "docs/inventory/ROOT_DOCUMENT_REFERENCE_CHECK.md",
    }

    for path in ROOT.rglob("*"):
        if not path.is_file() or path.suffix.lower() not in TEXT_SCAN_EXTENSIONS:
            continue
        relative = path.relative_to(ROOT).as_posix()
        if relative in report_paths or any(part in ignored_parts for part in path.parts):
            continue
        try:
            text = path.read_text(encoding="utf-8", errors="ignore")
        except OSError:
            continue
        for record in records:
            if record.source in text:
                references[record.source].append(relative)

    return {key: sorted(set(value)) for key, value in references.items() if value}


def write_reports(records: list[MoveRecord], references: dict[str, list[str]]) -> None:
    inventory_dir = DOCS / "inventory"
    inventory_dir.mkdir(parents=True, exist_ok=True)

    payload = {
        "generated_on": TODAY,
        "root_entrypoints_retained": sorted(ROOT_ENTRYPOINTS),
        "legal_files_retained_by_policy": True,
        "moved_count": len(records),
        "moves": [asdict(record) for record in records],
        "remaining_references": references,
    }
    (inventory_dir / "root_document_moves.json").write_text(
        json.dumps(payload, indent=2, ensure_ascii=False) + "\n", encoding="utf-8"
    )

    lines = [
        "# Root Document Consolidation",
        "",
        f"Generated: `{TODAY}`",
        "",
        "## Policy",
        "",
        "The repository root retains `README.md`, `AGENTS.md`, recognised legal files and machine/build configuration. Project documentation belongs under `docs/`.",
        "",
        f"Documents moved in this pass: **{len(records)}**.",
        "",
        "## Moves",
        "",
        "| Original root path | Destination | Classification | Bytes | SHA-256 |",
        "|---|---|---|---:|---|",
    ]
    for record in records:
        lines.append(f"| `{record.source}` | `{record.destination}` | {record.category} | {record.size} | `{record.sha256}` |")
    if not records:
        lines.append("| _None_ | _No root documents required moving_ | — | 0 | — |")
    lines.extend([
        "",
        "## Retained root files",
        "",
        "- `README.md` — repository and agent entry point",
        "- `AGENTS.md` — mandatory working agreement",
        "- `version.txt` — application/updater version marker",
        "- recognised licence/notice files",
        "- build, dependency, environment-template and machine configuration files",
        "",
        "See `ROOT_DOCUMENT_REFERENCE_CHECK.md` for references that may need manual link repair.",
        "",
    ])
    (inventory_dir / "ROOT_DOCUMENT_CONSOLIDATION.md").write_text("\n".join(lines), encoding="utf-8")

    ref_lines = [
        "# Root Document Reference Check",
        "",
        f"Generated: `{TODAY}`",
        "",
        "This report lists text files that still mention an old root document name after consolidation. A mention is not automatically a broken link; each result requires contextual review.",
        "",
    ]
    if not references:
        ref_lines.append("No remaining textual references to moved root document names were found.")
    else:
        for old_name, paths in sorted(references.items()):
            ref_lines.append(f"## `{old_name}`")
            ref_lines.append("")
            for path in paths:
                ref_lines.append(f"- `{path}`")
            ref_lines.append("")
    (inventory_dir / "ROOT_DOCUMENT_REFERENCE_CHECK.md").write_text("\n".join(ref_lines) + "\n", encoding="utf-8")


def main() -> None:
    records = move_documents()
    references = scan_references(records)
    write_reports(records, references)
    print(f"Moved {len(records)} root documents into docs/.")
    print(f"Remaining old-name reference groups: {len(references)}")


if __name__ == "__main__":
    main()
