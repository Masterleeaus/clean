#!/usr/bin/env python3
"""Inventory Interaction Engine, Wizard and five-tier AI runtimes.

This script is read-only with respect to application source. It writes evidence
under docs/inventory so documentation decisions can be grounded in the current
repository tree rather than filenames or branch history.
"""

from __future__ import annotations

import hashlib
import json
import re
import subprocess
from collections import Counter
from pathlib import Path
from typing import Any

ROOT = Path(__file__).resolve().parents[2]
OUTPUT_JSON = ROOT / "docs/inventory/INTERACTION_INTELLIGENCE_RUNTIME_INVENTORY.json"
OUTPUT_MD = ROOT / "docs/inventory/INTERACTION_INTELLIGENCE_RUNTIME_INVENTORY.md"

CANDIDATES = {
    "interaction_package_primary": ROOT / "packages/titanzero/interaction-engine",
    "interaction_package_hyphenated": ROOT / "packages/titan-zero/interaction-engine",
    "interaction_domain": ROOT / "app/Domains/InteractionEngine",
    "workcore_wizards": ROOT / "app/Domains/WorkCore/System/Modules/Wizards",
    "chatbot_ai_primary": ROOT / "app/Extensions/Chatbot/System/TitanAI",
    "chatbot_ai_secondary": ROOT / "app/Extensions/TitanZeroChatbot/System/TitanAI",
}

TEXT_EXTENSIONS = {
    ".php", ".json", ".md", ".txt", ".yml", ".yaml", ".xml", ".js", ".ts", ".tsx", ".jsx",
}
IGNORED_PARTS = {".git", "vendor", "node_modules", "storage", "bootstrap/cache"}
SEARCH_PATTERNS = [
    "InteractionServiceProvider",
    "titanzero/interaction-engine",
    "packages/titanzero/interaction-engine",
    "packages/titan-zero/interaction-engine",
    "interaction_engine_enabled",
    "WorkWizardsServiceProvider",
    "UniversalWizardEngine",
    "AuthorityBoundaryRegistry",
    "TitanZeroOrchestrator",
]


def git_sha() -> str:
    return subprocess.check_output(["git", "rev-parse", "HEAD"], cwd=ROOT, text=True).strip()


def file_hash(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def files_under(path: Path) -> list[Path]:
    if not path.is_dir():
        return []
    return sorted(p for p in path.rglob("*") if p.is_file())


def parse_composer(path: Path) -> dict[str, Any] | None:
    composer = path / "composer.json"
    if not composer.is_file():
        return None
    try:
        data = json.loads(composer.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError):
        return {"invalid": True}
    return {
        "name": data.get("name"),
        "type": data.get("type"),
        "autoload": data.get("autoload", {}),
        "laravel_providers": data.get("extra", {}).get("laravel", {}).get("providers", []),
        "require": data.get("require", {}),
    }


def summarise_root(path: Path) -> dict[str, Any]:
    files = files_under(path)
    extensions = Counter((p.suffix.lower() or "<none>") for p in files)
    top_level = Counter()
    for file in files:
        relative = file.relative_to(path)
        top_level[relative.parts[0]] += 1

    providers = [
        p.relative_to(ROOT).as_posix()
        for p in files
        if "ServiceProvider" in p.name or p.name.endswith("Provider.php")
    ]
    routes = [
        p.relative_to(ROOT).as_posix()
        for p in files
        if "routes" in {part.lower() for part in p.parts} or p.name.lower().startswith("routes")
    ]
    migrations = [
        p.relative_to(ROOT).as_posix()
        for p in files
        if "migration" in "/".join(part.lower() for part in p.parts)
    ]
    tests = [
        p.relative_to(ROOT).as_posix()
        for p in files
        if "tests" in {part.lower() for part in p.parts} or p.name.endswith("Test.php")
    ]
    tiers = Counter()
    for file in files:
        relative_lower = file.relative_to(path).as_posix().lower()
        for tier in ("tier0", "tier1", "tier2", "tier3", "tier4"):
            if tier in relative_lower:
                tiers[tier] += 1

    return {
        "path": path.relative_to(ROOT).as_posix(),
        "exists": path.exists(),
        "is_directory": path.is_dir(),
        "file_count": len(files),
        "byte_count": sum(p.stat().st_size for p in files),
        "extensions": dict(sorted(extensions.items())),
        "top_level_entries": dict(sorted(top_level.items())),
        "composer": parse_composer(path),
        "provider_files": providers,
        "route_files": routes,
        "migration_files": migrations,
        "test_files": tests,
        "tier_file_counts": dict(sorted(tiers.items())),
    }


def compare_trees(left: Path, right: Path) -> dict[str, Any]:
    left_files = {p.relative_to(left).as_posix(): p for p in files_under(left)}
    right_files = {p.relative_to(right).as_posix(): p for p in files_under(right)}
    common = sorted(set(left_files) & set(right_files))
    identical: list[str] = []
    different: list[str] = []
    for relative in common:
        if file_hash(left_files[relative]) == file_hash(right_files[relative]):
            identical.append(relative)
        else:
            different.append(relative)
    return {
        "left": left.relative_to(ROOT).as_posix(),
        "right": right.relative_to(ROOT).as_posix(),
        "common_file_count": len(common),
        "identical_file_count": len(identical),
        "different_file_count": len(different),
        "only_left_count": len(set(left_files) - set(right_files)),
        "only_right_count": len(set(right_files) - set(left_files)),
        "different_files": different,
        "only_left_files": sorted(set(left_files) - set(right_files)),
        "only_right_files": sorted(set(right_files) - set(left_files)),
    }


def root_composer_status() -> dict[str, Any]:
    composer_path = ROOT / "composer.json"
    data = json.loads(composer_path.read_text(encoding="utf-8"))
    repositories = [
        entry.get("url")
        for entry in data.get("repositories", [])
        if isinstance(entry, dict) and entry.get("type") == "path"
    ]
    required = data.get("require", {})
    return {
        "path_repositories": repositories,
        "requires_interaction_package": "titanzero/interaction-engine" in required,
        "interaction_requirement": required.get("titanzero/interaction-engine"),
        "primary_path_registered": "./packages/titanzero/interaction-engine" in repositories or "packages/titanzero/interaction-engine" in repositories,
        "hyphenated_path_registered": "./packages/titan-zero/interaction-engine" in repositories or "packages/titan-zero/interaction-engine" in repositories,
    }


def text_references() -> dict[str, list[str]]:
    results = {pattern: [] for pattern in SEARCH_PATTERNS}
    excluded_prefixes = (
        "docs/reference/", "docs/archive/", "source-packs/", "tools/donor-sources/",
    )
    for path in ROOT.rglob("*"):
        if not path.is_file() or path.suffix.lower() not in TEXT_EXTENSIONS:
            continue
        relative = path.relative_to(ROOT).as_posix()
        if relative.startswith(excluded_prefixes):
            continue
        if any(part in IGNORED_PARTS for part in path.parts):
            continue
        if relative in {OUTPUT_JSON.relative_to(ROOT).as_posix(), OUTPUT_MD.relative_to(ROOT).as_posix()}:
            continue
        try:
            text = path.read_text(encoding="utf-8", errors="ignore")
        except OSError:
            continue
        for pattern in SEARCH_PATTERNS:
            if pattern in text:
                results[pattern].append(relative)
    return {pattern: sorted(set(paths)) for pattern, paths in results.items()}


def package_name_collisions(roots: dict[str, dict[str, Any]]) -> dict[str, list[str]]:
    names: dict[str, list[str]] = {}
    for key, summary in roots.items():
        composer = summary.get("composer") or {}
        name = composer.get("name") if isinstance(composer, dict) else None
        if isinstance(name, str) and name:
            names.setdefault(name, []).append(key)
    return {name: keys for name, keys in names.items() if len(keys) > 1}


def derive_findings(data: dict[str, Any]) -> list[dict[str, str]]:
    findings: list[dict[str, str]] = []
    roots = data["roots"]
    package_compare = data["comparisons"]["interaction_package_paths"]
    root_composer = data["root_composer"]

    primary_count = roots["interaction_package_primary"]["file_count"]
    secondary_count = roots["interaction_package_hyphenated"]["file_count"]
    if primary_count > 1 and secondary_count == 1 and package_compare["only_right_files"] == ["composer.json"]:
        findings.append({
            "classification": "confirmed duplicate package root",
            "finding": "packages/titan-zero/interaction-engine contains only a second composer.json for the same package name, while packages/titanzero/interaction-engine contains the implementation.",
        })

    if not root_composer["requires_interaction_package"] or not root_composer["primary_path_registered"]:
        findings.append({
            "classification": "confirmed host wiring gap",
            "finding": "The root Composer project does not both register and require titanzero/interaction-engine, so package auto-discovery cannot be relied on from a clean install.",
        })

    workcore = roots["workcore_wizards"]
    if workcore["exists"] and workcore["provider_files"]:
        findings.append({
            "classification": "confirmed active bounded runtime candidate",
            "finding": "WorkCore contains its own Wizard module and provider; this runtime governs operational wizard capabilities inside WorkCore and must not be conflated with the universal Interaction Engine package.",
        })

    chatbot_comparison = data["comparisons"]["chatbot_ai_paths"]
    if roots["chatbot_ai_primary"]["exists"] and roots["chatbot_ai_secondary"]["exists"]:
        findings.append({
            "classification": "duplicate runtime risk",
            "finding": (
                "Both Chatbot and TitanZeroChatbot contain TitanAI trees. "
                f"They share {chatbot_comparison['common_file_count']} relative files, with "
                f"{chatbot_comparison['identical_file_count']} identical and "
                f"{chatbot_comparison['different_file_count']} divergent copies."
            ),
        })

    return findings


def build_markdown(data: dict[str, Any]) -> str:
    lines = [
        "# Interaction, Wizard and Five-Tier Intelligence Runtime Inventory",
        "",
        f"Source commit: `{data['source_commit']}`",
        "",
        "This is an evidence inventory. It does not declare a runtime active merely because files exist.",
        "",
        "## Runtime roots",
        "",
        "| Key | Path | Exists | Files | Bytes | Providers | Routes | Migrations | Tests |",
        "|---|---|---:|---:|---:|---:|---:|---:|---:|",
    ]
    for key, summary in data["roots"].items():
        lines.append(
            f"| `{key}` | `{summary['path']}` | {str(summary['exists']).lower()} | "
            f"{summary['file_count']} | {summary['byte_count']} | {len(summary['provider_files'])} | "
            f"{len(summary['route_files'])} | {len(summary['migration_files'])} | {len(summary['test_files'])} |"
        )

    lines.extend(["", "## Composer and package identity", ""])
    for key in ("interaction_package_primary", "interaction_package_hyphenated"):
        composer = data["roots"][key].get("composer")
        lines.append(f"### `{key}`")
        lines.append("")
        lines.append(f"- Path: `{data['roots'][key]['path']}`")
        lines.append(f"- Package name: `{(composer or {}).get('name')}`")
        lines.append(f"- Laravel providers: `{(composer or {}).get('laravel_providers', [])}`")
        lines.append("")

    root_composer = data["root_composer"]
    lines.extend([
        "### Root Composer activation",
        "",
        f"- Requires package: **{str(root_composer['requires_interaction_package']).lower()}**",
        f"- Registers primary path: **{str(root_composer['primary_path_registered']).lower()}**",
        f"- Registers hyphenated path: **{str(root_composer['hyphenated_path_registered']).lower()}**",
        "",
        "## Tree comparisons",
        "",
    ])
    for name, comparison in data["comparisons"].items():
        lines.extend([
            f"### `{name}`",
            "",
            f"- Left: `{comparison['left']}`",
            f"- Right: `{comparison['right']}`",
            f"- Common files: **{comparison['common_file_count']}**",
            f"- Identical files: **{comparison['identical_file_count']}**",
            f"- Different files: **{comparison['different_file_count']}**",
            f"- Only left: **{comparison['only_left_count']}**",
            f"- Only right: **{comparison['only_right_count']}**",
            "",
        ])

    lines.extend(["## Five-tier file counts", ""])
    for key in ("chatbot_ai_primary", "chatbot_ai_secondary"):
        lines.append(f"- `{key}`: `{data['roots'][key]['tier_file_counts']}`")

    lines.extend(["", "## Findings", ""])
    for finding in data["findings"]:
        lines.append(f"- **{finding['classification']}:** {finding['finding']}")

    lines.extend([
        "",
        "## Required disposition rule",
        "",
        "- Keep one physical Composer package root for `titanzero/interaction-engine`.",
        "- Treat WorkCore Wizards as an operational-domain module, not a replacement Interaction Engine.",
        "- Treat Chatbot/PWA AI code as presentation/device orchestration or compatibility code until provider and call-path evidence proves host authority.",
        "- Do not activate two TitanAI trees or two package roots with the same namespaces.",
        "- Preserve unique metadata, tests and contracts before deleting a duplicate path.",
        "",
        "Full file-level comparisons and reference locations are in the JSON inventory.",
        "",
    ])
    return "\n".join(lines)


def main() -> None:
    roots = {key: summarise_root(path) for key, path in CANDIDATES.items()}
    data: dict[str, Any] = {
        "source_commit": git_sha(),
        "roots": roots,
        "root_composer": root_composer_status(),
        "package_name_collisions": package_name_collisions(roots),
        "comparisons": {
            "interaction_package_paths": compare_trees(
                CANDIDATES["interaction_package_primary"],
                CANDIDATES["interaction_package_hyphenated"],
            ),
            "chatbot_ai_paths": compare_trees(
                CANDIDATES["chatbot_ai_primary"],
                CANDIDATES["chatbot_ai_secondary"],
            ),
        },
        "text_references": text_references(),
    }
    data["findings"] = derive_findings(data)
    OUTPUT_JSON.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_JSON.write_text(json.dumps(data, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
    OUTPUT_MD.write_text(build_markdown(data), encoding="utf-8")
    print(f"Wrote {OUTPUT_JSON.relative_to(ROOT)}")
    print(f"Wrote {OUTPUT_MD.relative_to(ROOT)}")


if __name__ == "__main__":
    main()
