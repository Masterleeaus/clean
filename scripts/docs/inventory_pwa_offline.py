#!/usr/bin/env python3
"""Inventory the duplicated Chatbot/PWA extension trees and offline runtimes."""

from __future__ import annotations

import hashlib
import json
import re
import subprocess
from collections import Counter, defaultdict
from pathlib import Path
from typing import Any

ROOT = Path(__file__).resolve().parents[2]
PRIMARY = ROOT / "app/Extensions/Chatbot"
SECONDARY = ROOT / "app/Extensions/TitanZeroChatbot"
OUTPUT_JSON = ROOT / "docs/inventory/PWA_OFFLINE_RUNTIME_INVENTORY.json"
OUTPUT_MD = ROOT / "docs/inventory/PWA_OFFLINE_RUNTIME_INVENTORY.md"

TEXT_EXTENSIONS = {
    ".php", ".json", ".md", ".txt", ".yml", ".yaml", ".xml", ".js", ".mjs", ".cjs",
    ".ts", ".tsx", ".jsx", ".vue", ".blade.php", ".scss", ".css", ".html",
}
IGNORED_PARTS = {".git", "vendor", "node_modules", "storage", "bootstrap/cache"}

CATEGORY_PATTERNS = {
    "service_worker": [r"service[-_]?worker", r"serviceworker", r"(^|/)sw\.(js|ts)$"],
    "manifest": [r"manifest", r"extension\.json$", r"module\.json$", r"webmanifest$"],
    "indexeddb": [r"indexeddb", r"indexed-db", r"\bidb\b", r"dexie", r"local[-_]?database"],
    "vault_crypto": [r"vault", r"crypto", r"encrypt", r"decrypt", r"keyring", r"key-store", r"keystore"],
    "outbox_queue": [r"outbox", r"offline[-_]?queue", r"pending[-_]?mutation", r"command[-_]?queue"],
    "sync_conflict": [r"sync", r"conflict", r"reconcile", r"replay", r"watermark", r"checkpoint"],
    "offline_cache": [r"offline", r"cache", r"precache", r"workbox"],
    "device_identity": [r"device", r"node[-_]?identity", r"installation[-_]?id", r"client[-_]?uuid"],
    "background_push": [r"background[-_]?sync", r"push[-_]?subscription", r"notification", r"web[-_]?push"],
}

REFERENCE_TERMS = {
    "primary_namespace": "App\\Extensions\\Chatbot",
    "secondary_namespace": "App\\Extensions\\TitanZeroChatbot",
    "primary_path": "app/Extensions/Chatbot",
    "secondary_path": "app/Extensions/TitanZeroChatbot",
    "primary_provider": "App\\Extensions\\Chatbot\\System\\ChatbotServiceProvider",
    "secondary_provider": "App\\Extensions\\TitanZeroChatbot\\System\\ChatbotServiceProvider",
}


def git_sha() -> str:
    return subprocess.check_output(["git", "rev-parse", "HEAD"], cwd=ROOT, text=True).strip()


def file_hash(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def files_under(root: Path) -> list[Path]:
    if not root.is_dir():
        return []
    return sorted(path for path in root.rglob("*") if path.is_file())


def normal_suffix(path: Path) -> str:
    name = path.name.lower()
    if name.endswith(".blade.php"):
        return ".blade.php"
    return path.suffix.lower() or "<none>"


def is_text(path: Path) -> bool:
    return normal_suffix(path) in TEXT_EXTENSIONS or path.suffix.lower() in TEXT_EXTENSIONS


def read_text(path: Path) -> str:
    try:
        return path.read_text(encoding="utf-8", errors="ignore")
    except OSError:
        return ""


def matching_categories(path: Path, root: Path) -> list[str]:
    relative = path.relative_to(root).as_posix().lower()
    text = read_text(path).lower() if is_text(path) and path.stat().st_size <= 2_000_000 else ""
    haystack = relative + "\n" + text
    categories = []
    for category, patterns in CATEGORY_PATTERNS.items():
        if any(re.search(pattern, haystack, flags=re.IGNORECASE) for pattern in patterns):
            categories.append(category)
    return categories


def parse_json(path: Path) -> Any:
    if not path.is_file():
        return None
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError):
        return {"invalid": True}


def extension_metadata(root: Path) -> dict[str, Any]:
    candidates = [
        root / "extension.json",
        root / "module.json",
        root / "manifest.json",
        root / "package.json",
        root / "composer.json",
    ]
    result = {}
    for path in candidates:
        if path.is_file():
            result[path.name] = parse_json(path)
    return result


def summarise(root: Path) -> dict[str, Any]:
    files = files_under(root)
    categories: dict[str, list[str]] = defaultdict(list)
    for path in files:
        for category in matching_categories(path, root):
            categories[category].append(path.relative_to(ROOT).as_posix())

    providers = [
        path.relative_to(ROOT).as_posix()
        for path in files
        if path.name.endswith("ServiceProvider.php") or path.name.endswith("Provider.php")
    ]
    routes = [
        path.relative_to(ROOT).as_posix()
        for path in files
        if "routes" in {part.lower() for part in path.parts} and path.suffix.lower() == ".php"
    ]
    migrations = [
        path.relative_to(ROOT).as_posix()
        for path in files
        if "migrations" in {part.lower() for part in path.parts}
    ]
    tests = [
        path.relative_to(ROOT).as_posix()
        for path in files
        if "tests" in {part.lower() for part in path.parts} or "test" in path.stem.lower()
    ]
    top_level = Counter(path.relative_to(root).parts[0] for path in files)
    extensions = Counter(normal_suffix(path) for path in files)

    return {
        "path": root.relative_to(ROOT).as_posix(),
        "exists": root.is_dir(),
        "file_count": len(files),
        "byte_count": sum(path.stat().st_size for path in files),
        "extensions": dict(sorted(extensions.items())),
        "top_level_entries": dict(sorted(top_level.items())),
        "metadata": extension_metadata(root),
        "provider_files": providers,
        "route_files": routes,
        "migration_files": migrations,
        "test_files": tests,
        "categories": {key: sorted(value) for key, value in sorted(categories.items())},
        "category_counts": {key: len(value) for key, value in sorted(categories.items())},
    }


def compare(left: Path, right: Path) -> dict[str, Any]:
    left_files = {path.relative_to(left).as_posix(): path for path in files_under(left)}
    right_files = {path.relative_to(right).as_posix(): path for path in files_under(right)}
    common = sorted(set(left_files) & set(right_files))
    identical, different = [], []
    for relative in common:
        if file_hash(left_files[relative]) == file_hash(right_files[relative]):
            identical.append(relative)
        else:
            different.append(relative)
    only_left = sorted(set(left_files) - set(right_files))
    only_right = sorted(set(right_files) - set(left_files))
    return {
        "common_file_count": len(common),
        "identical_file_count": len(identical),
        "different_file_count": len(different),
        "only_primary_count": len(only_left),
        "only_secondary_count": len(only_right),
        "different_files": different,
        "only_primary_files": only_left,
        "only_secondary_files": only_right,
    }


def repository_references() -> dict[str, list[str]]:
    results = {key: [] for key in REFERENCE_TERMS}
    excluded_prefixes = (
        "docs/archive/", "docs/reference/", "source-packs/", "tools/donor-sources/",
        PRIMARY.relative_to(ROOT).as_posix() + "/",
        SECONDARY.relative_to(ROOT).as_posix() + "/",
    )
    for path in ROOT.rglob("*"):
        if not path.is_file() or not is_text(path):
            continue
        relative = path.relative_to(ROOT).as_posix()
        if relative.startswith(excluded_prefixes) or any(part in IGNORED_PARTS for part in path.parts):
            continue
        if relative in {OUTPUT_JSON.relative_to(ROOT).as_posix(), OUTPUT_MD.relative_to(ROOT).as_posix()}:
            continue
        text = read_text(path)
        for key, term in REFERENCE_TERMS.items():
            if term in text:
                results[key].append(relative)
    return {key: sorted(set(paths)) for key, paths in results.items()}


def service_worker_registrations() -> list[dict[str, str]]:
    registrations = []
    patterns = [
        re.compile(r"serviceWorker\.register\(([^\n;]+)", re.IGNORECASE),
        re.compile(r"navigator\.serviceWorker", re.IGNORECASE),
    ]
    for path in ROOT.rglob("*"):
        if not path.is_file() or not is_text(path):
            continue
        if any(part in IGNORED_PARTS for part in path.parts):
            continue
        relative = path.relative_to(ROOT).as_posix()
        if relative.startswith(("docs/reference/", "docs/archive/", "source-packs/", "tools/donor-sources/")):
            continue
        text = read_text(path)
        if not any(pattern.search(text) for pattern in patterns):
            continue
        snippets = []
        for line in text.splitlines():
            if "serviceWorker" in line or "service-worker" in line.lower():
                snippets.append(line.strip()[:300])
                if len(snippets) >= 5:
                    break
        registrations.append({"path": relative, "snippets": " | ".join(snippets)})
    return sorted(registrations, key=lambda item: item["path"])


def derive_findings(data: dict[str, Any]) -> list[dict[str, str]]:
    findings: list[dict[str, str]] = []
    comparison = data["comparison"]
    references = data["repository_references"]

    if comparison["identical_file_count"] and comparison["different_file_count"] == 0 and comparison["only_primary_count"] == 0 and comparison["only_secondary_count"] == 0:
        findings.append({
            "classification": "confirmed exact duplicate extension",
            "finding": "The complete Chatbot and TitanZeroChatbot extension trees are byte-identical and have identical relative file sets.",
        })
    elif comparison["identical_file_count"]:
        findings.append({
            "classification": "partial duplicate extension",
            "finding": (
                f"The extension trees share {comparison['common_file_count']} files; "
                f"{comparison['identical_file_count']} are identical and {comparison['different_file_count']} differ."
            ),
        })

    primary_refs = len(references.get("primary_provider", [])) + len(references.get("primary_namespace", []))
    secondary_refs = len(references.get("secondary_provider", [])) + len(references.get("secondary_namespace", []))
    if primary_refs > secondary_refs:
        findings.append({
            "classification": "canonical activation evidence",
            "finding": "Repository bootstrap and source references favour App\\Extensions\\Chatbot over App\\Extensions\\TitanZeroChatbot.",
        })
    if secondary_refs:
        findings.append({
            "classification": "compatibility dependency",
            "finding": f"The secondary extension namespace/path still has {secondary_refs} non-reference textual references and cannot be removed without focused dependency repair.",
        })

    for key in ("service_worker", "indexeddb", "vault_crypto", "outbox_queue", "sync_conflict"):
        primary_count = data["primary"]["category_counts"].get(key, 0)
        secondary_count = data["secondary"]["category_counts"].get(key, 0)
        if primary_count or secondary_count:
            findings.append({
                "classification": "offline subsystem evidence",
                "finding": f"{key} candidates: primary {primary_count}, secondary {secondary_count}; file-level paths are recorded in the JSON inventory.",
            })

    return findings


def markdown(data: dict[str, Any]) -> str:
    primary = data["primary"]
    secondary = data["secondary"]
    comparison = data["comparison"]
    lines = [
        "# PWA, Offline and Chatbot Extension Runtime Inventory",
        "",
        f"Source commit: `{data['source_commit']}`",
        "",
        "This inventory distinguishes source presence, duplication and bootstrap evidence from verified runtime activation.",
        "",
        "## Extension roots",
        "",
        "| Extension | Path | Files | Bytes | Providers | Routes | Migrations | Tests |",
        "|---|---|---:|---:|---:|---:|---:|---:|",
        f"| Primary | `{primary['path']}` | {primary['file_count']} | {primary['byte_count']} | {len(primary['provider_files'])} | {len(primary['route_files'])} | {len(primary['migration_files'])} | {len(primary['test_files'])} |",
        f"| Secondary | `{secondary['path']}` | {secondary['file_count']} | {secondary['byte_count']} | {len(secondary['provider_files'])} | {len(secondary['route_files'])} | {len(secondary['migration_files'])} | {len(secondary['test_files'])} |",
        "",
        "## Full-tree comparison",
        "",
        f"- Common relative files: **{comparison['common_file_count']}**",
        f"- Byte-identical common files: **{comparison['identical_file_count']}**",
        f"- Divergent common files: **{comparison['different_file_count']}**",
        f"- Primary-only files: **{comparison['only_primary_count']}**",
        f"- Secondary-only files: **{comparison['only_secondary_count']}**",
        "",
        "## Offline subsystem candidates",
        "",
        "| Category | Primary files | Secondary files |",
        "|---|---:|---:|",
    ]
    for category in CATEGORY_PATTERNS:
        lines.append(
            f"| `{category}` | {primary['category_counts'].get(category, 0)} | {secondary['category_counts'].get(category, 0)} |"
        )

    lines.extend([
        "",
        "## External activation references",
        "",
    ])
    for key, paths in data["repository_references"].items():
        lines.append(f"- `{key}`: **{len(paths)}** references")

    lines.extend([
        "",
        "## Service-worker registration candidates",
        "",
        f"- Files containing registration/runtime references: **{len(data['service_worker_registrations'])}**",
        "",
        "## Findings",
        "",
    ])
    for finding in data["findings"]:
        lines.append(f"- **{finding['classification']}:** {finding['finding']}")

    lines.extend([
        "",
        "## Required disposition rule",
        "",
        "- `app/Extensions/Chatbot` is the intended canonical extension unless bootstrap evidence proves otherwise.",
        "- `app/Extensions/TitanZeroChatbot` remains frozen compatibility/reference material until every external caller, provider, route, asset and migration dependency is traced.",
        "- Do not activate two byte-identical provider or migration trees.",
        "- Do not delete unsynchronised local data or change IndexedDB schemas without explicit migration and recovery behaviour.",
        "- Service-worker caches must not contain credentials, provider secrets or sensitive API responses.",
        "- Offline operational mutations must reconcile through canonical WorkCore actions.",
        "",
        "Full paths, metadata, differences, references and registration snippets are stored in the JSON inventory.",
        "",
    ])
    return "\n".join(lines)


def main() -> None:
    data: dict[str, Any] = {
        "source_commit": git_sha(),
        "primary": summarise(PRIMARY),
        "secondary": summarise(SECONDARY),
        "comparison": compare(PRIMARY, SECONDARY),
        "repository_references": repository_references(),
        "service_worker_registrations": service_worker_registrations(),
    }
    data["findings"] = derive_findings(data)
    OUTPUT_JSON.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_JSON.write_text(json.dumps(data, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
    OUTPUT_MD.write_text(markdown(data), encoding="utf-8")
    print(f"Wrote {OUTPUT_JSON.relative_to(ROOT)}")
    print(f"Wrote {OUTPUT_MD.relative_to(ROOT)}")


if __name__ == "__main__":
    main()
