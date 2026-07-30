#!/usr/bin/env python3
"""Validate the Titan Agent OS bootstrap structure without third-party packages."""

from __future__ import annotations

import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[3]
TITAN = ROOT / ".titan"

REQUIRED_PATHS = [
    ROOT / "README.md",
    ROOT / "AGENTS.md",
    ROOT / "docs" / "README.md",
    TITAN / "README.md",
    TITAN / "MANDATE.md",
    TITAN / "os.yaml",
    TITAN / "kernel" / "constitution" / "README.md",
    TITAN / "kernel" / "schemas" / "object-metadata.schema.json",
    TITAN / "kernel" / "schemas" / "document-source.schema.json",
    TITAN / "kernel" / "schemas" / "agent-manifest.schema.json",
    TITAN / "kernel" / "schemas" / "provider-manifest.schema.json",
    TITAN / "kernel" / "schemas" / "event-envelope.schema.json",
    TITAN / "kernel" / "schemas" / "mailbox-message.schema.json",
    TITAN / "kernel" / "schemas" / "decision-record.schema.json",
    TITAN / "registry" / "README.md",
    TITAN / "registry" / "documentation.yaml",
    TITAN / "control-plane" / "claude" / "README.md",
    TITAN / "execution-plane" / "mailboxes" / "README.md",
    TITAN / "documentation" / "README.md",
    TITAN / "documentation" / "agents" / "START-HERE.md",
    TITAN / "documentation" / "status" / "current.md",
    TITAN / "documentation" / "chronicle" / "timeline.md",
    TITAN / "documentation" / "system" / "README.md",
]

MARKDOWN_ENTRY_POINTS = [
    ROOT / "README.md",
    ROOT / "AGENTS.md",
    ROOT / "docs" / "README.md",
    TITAN / "README.md",
    TITAN / "documentation" / "README.md",
]

LINK_RE = re.compile(r"\[[^\]]+\]\(([^)]+)\)")
REGISTRY_PATH_RE = re.compile(r"^\s+path:\s+([^#\n]+?)\s*$", re.MULTILINE)


def validate_required_paths(errors: list[str]) -> None:
    for path in REQUIRED_PATHS:
        if not path.exists():
            errors.append(f"missing required path: {path.relative_to(ROOT)}")


def validate_json_schemas(errors: list[str]) -> int:
    count = 0
    for path in sorted((TITAN / "kernel" / "schemas").glob("*.json")):
        count += 1
        try:
            data = json.loads(path.read_text(encoding="utf-8"))
        except Exception as exc:  # noqa: BLE001 - validator reports exact parse failure
            errors.append(f"invalid JSON {path.relative_to(ROOT)}: {exc}")
            continue
        if data.get("$schema") != "https://json-schema.org/draft/2020-12/schema":
            errors.append(f"unexpected schema draft: {path.relative_to(ROOT)}")
        if not data.get("$id") or not data.get("title"):
            errors.append(f"schema missing $id/title: {path.relative_to(ROOT)}")
    if count == 0:
        errors.append("no JSON schemas found")
    return count


def validate_yaml_contracts(errors: list[str]) -> None:
    os_text = (TITAN / "os.yaml").read_text(encoding="utf-8")
    for marker in ["id: titan.agent-os", "version: 1.0.0-bootstrap", "mode: federated"]:
        if marker not in os_text:
            errors.append(f".titan/os.yaml missing marker: {marker}")

    registry_path = TITAN / "registry" / "documentation.yaml"
    registry_text = registry_path.read_text(encoding="utf-8")
    for marker in ["registry_id: titan.documentation.sources", "duplicate_manual_authority: prohibited", "sources:"]:
        if marker not in registry_text:
            errors.append(f"documentation registry missing marker: {marker}")

    for value in REGISTRY_PATH_RE.findall(registry_text):
        candidate = value.strip().strip('"\'')
        if candidate.startswith("external:"):
            continue
        if not (ROOT / candidate).exists():
            errors.append(f"documentation registry path does not exist: {candidate}")


def validate_markdown_links(errors: list[str]) -> int:
    checked = 0
    for path in MARKDOWN_ENTRY_POINTS:
        text = path.read_text(encoding="utf-8")
        for target in LINK_RE.findall(text):
            target = target.strip()
            if not target or target.startswith(("http://", "https://", "mailto:", "#")):
                continue
            clean = target.split("#", 1)[0].split("?", 1)[0]
            if not clean:
                continue
            checked += 1
            destination = (path.parent / clean).resolve()
            try:
                destination.relative_to(ROOT.resolve())
            except ValueError:
                errors.append(f"link escapes repository: {path.relative_to(ROOT)} -> {target}")
                continue
            if not destination.exists():
                errors.append(f"broken local link: {path.relative_to(ROOT)} -> {target}")
    return checked


def validate_mandate(errors: list[str]) -> None:
    mandate = (TITAN / "MANDATE.md").read_text(encoding="utf-8")
    required_headings = [
        "# Titan Agent OS",
        "## Claude Architecture Authority",
        "## Human Authority",
        "## Kernel",
        "## Control Plane",
        "## Execution Plane",
        "## Documentation Layer",
        "## Long-Term Vision",
    ]
    for heading in required_headings:
        if heading not in mandate:
            errors.append(f"MANDATE.md missing heading: {heading}")


def validate_generated_boundary(errors: list[str]) -> None:
    system_dir = TITAN / "documentation" / "system"
    manual_files = [p for p in system_dir.rglob("*") if p.is_file() and p.name != "README.md"]
    if manual_files:
        errors.append("bootstrap system documentation contains unexpected manually-authored files: " + ", ".join(str(p.relative_to(ROOT)) for p in manual_files))


def main() -> int:
    errors: list[str] = []
    validate_required_paths(errors)
    if errors:
        for error in errors:
            print(f"ERROR: {error}")
        return 1

    schema_count = validate_json_schemas(errors)
    validate_yaml_contracts(errors)
    link_count = validate_markdown_links(errors)
    validate_mandate(errors)
    validate_generated_boundary(errors)

    if errors:
        for error in errors:
            print(f"ERROR: {error}")
        print(f"FAILED: {len(errors)} validation error(s)")
        return 1

    print(f"OK: {len(REQUIRED_PATHS)} required paths present")
    print(f"OK: {schema_count} JSON schemas parsed")
    print(f"OK: {link_count} local Markdown links resolved")
    print("OK: YAML contracts contain required bootstrap markers")
    print("OK: Claude mandate contains required sections")
    print("OK: generated-system boundary contains no unexpected manual output")
    return 0


if __name__ == "__main__":
    sys.exit(main())
