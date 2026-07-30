#!/usr/bin/env python3
"""Inventory Titan Zero/MagicAI extension manifests, providers and lifecycle risks."""

from __future__ import annotations

import hashlib
import json
import re
import subprocess
from collections import Counter, defaultdict
from pathlib import Path
from typing import Any

ROOT = Path(__file__).resolve().parents[2]
EXTENSIONS = ROOT / "app/Extensions"
MARKETPLACE_PROVIDER = ROOT / "app/Domains/Marketplace/MarketplaceServiceProvider.php"
INSTALL_SERVICE = ROOT / "app/Domains/Marketplace/Services/ExtensionInstallService.php"
UNINSTALL_SERVICE = ROOT / "app/Domains/Marketplace/Services/ExtensionUninstallService.php"
FEATURE_FLAGS = ROOT / "config/titan-zero.php"
OUTPUT_JSON = ROOT / "docs/inventory/EXTENSION_PLATFORM_INVENTORY.json"
OUTPUT_MD = ROOT / "docs/inventory/EXTENSION_PLATFORM_INVENTORY.md"

TEXT_EXTENSIONS = {".php", ".json", ".md", ".txt", ".yml", ".yaml", ".xml", ".js", ".ts", ".tsx", ".jsx", ".vue"}
MANIFEST_NAMES = ["extension.json", "module.json", "manifest.json", "composer.json", "package.json"]


def git_sha() -> str:
    return subprocess.check_output(["git", "rev-parse", "HEAD"], cwd=ROOT, text=True).strip()


def hash_file(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def files_under(path: Path) -> list[Path]:
    if not path.is_dir():
        return []
    return sorted(item for item in path.rglob("*") if item.is_file())


def safe_json(path: Path) -> Any:
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError):
        return {"invalid": True}


def php_symbols(path: Path) -> list[str]:
    if path.suffix.lower() != ".php":
        return []
    text = path.read_text(encoding="utf-8", errors="ignore")
    namespace_match = re.search(r"\bnamespace\s+([^;]+);", text)
    namespace = namespace_match.group(1).strip() if namespace_match else ""
    symbols = []
    for match in re.finditer(r"\b(?:final\s+|abstract\s+|readonly\s+)*(class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)", text):
        name = match.group(2)
        symbols.append(f"{namespace}\\{name}" if namespace else name)
    return symbols


def tree_fingerprint(root: Path) -> str:
    digest = hashlib.sha256()
    for path in files_under(root):
        relative = path.relative_to(root).as_posix().encode()
        digest.update(relative)
        digest.update(b"\0")
        digest.update(hash_file(path).encode())
        digest.update(b"\0")
    return digest.hexdigest()


def parse_marketplace_provider() -> dict[str, Any]:
    text = MARKETPLACE_PROVIDER.read_text(encoding="utf-8", errors="ignore")
    imports = {}
    for match in re.finditer(r"^use\s+([^;]+);", text, flags=re.MULTILINE):
        fqcn = match.group(1).strip()
        short = fqcn.split("\\")[-1]
        imports[short] = fqcn

    array_match = re.search(
        r"public\s+static\s+array\s+\$extensionProviders\s*=\s*\[(.*?)\n\s*\];",
        text,
        flags=re.DOTALL,
    )
    providers: dict[str, str] = {}
    if array_match:
        for match in re.finditer(r"['\"]([^'\"]+)['\"]\s*=>\s*([A-Za-z_][A-Za-z0-9_]*)::class", array_match.group(1)):
            slug, short = match.groups()
            providers[slug] = imports.get(short, short)

    return {
        "provider_count": len(providers),
        "providers": providers,
        "extension_discovery_guarded": "extensionDiscoveryEnabled()" in text,
        "registers_all_mapped_providers": "foreach (static::$extensionProviders as $provider)" in text,
        "install_route_uses_get": bool(re.search(r"->get\(['\"]dashboard/marketplace/extension/\{slug\}/install", text)),
        "uninstall_route_uses_get": bool(re.search(r"->get\(['\"]dashboard/marketplace/extension/\{slug\}/uninstall", text)),
        "route_middleware": re.findall(r"'middleware'\s*=>\s*\[([^\]]+)\]", text),
    }


def lifecycle_status() -> dict[str, Any]:
    install = INSTALL_SERVICE.read_text(encoding="utf-8", errors="ignore")
    uninstall = UNINSTALL_SERVICE.read_text(encoding="utf-8", errors="ignore")
    return {
        "install": {
            "downloads_remote_zip": "->install(" in install and "$response->body()" in install,
            "zip_extract_to_direct": "$this->archive->extractTo($extensionFolderPath)" in install,
            "zip_entry_validation_detected": any(token in install for token in ["getNameIndex", "statIndex", "realpath", "../", "isLink"]),
            "signature_verification_detected": any(token in install.lower() for token in ["signature", "publickey", "sodium_crypto_sign", "openssl_verify"]),
            "chmod_0777": "0777" in install,
            "forced_migration": "Artisan::call('migrate', ['--force' => true])" in install,
            "forced_asset_publish": "'--force' => true" in install and "vendor:publish" in install,
            "clears_caches": "optimize:clear" in install or "cache:clear" in install,
            "transaction_detected": "DB::transaction" in install or "->transaction(" in install,
            "rollback_path_detected": any(token in install.lower() for token in ["rollback", "backup", "restore"]),
            "chmod_folder": re.findall(r"chmod\([^;]+", install),
        },
        "uninstall": {
            "deletes_directory": "deleteDirectory" in uninstall,
            "clears_cache": "cache:clear" in uninstall,
            "database_rollback_detected": any(token in uninstall.lower() for token in ["migrate:rollback", "down(", "rollback"]),
            "exceptions_swallowed": bool(re.search(r"catch\s*\([^)]*\)\s*\{\s*\}", uninstall, flags=re.DOTALL)),
            "provider_uninstall_hook": "MarketplaceServiceProvider::uninstallExtension" in uninstall,
        },
    }


def summarise_extension(path: Path) -> dict[str, Any]:
    files = files_under(path)
    manifests = {}
    for name in MANIFEST_NAMES:
        candidate = path / name
        if candidate.is_file():
            manifests[name] = safe_json(candidate)

    providers = [item.relative_to(ROOT).as_posix() for item in files if item.name.endswith("ServiceProvider.php")]
    migrations = [item.relative_to(ROOT).as_posix() for item in files if "migrations" in {part.lower() for part in item.parts}]
    routes = [item.relative_to(ROOT).as_posix() for item in files if "routes" in {part.lower() for part in item.parts} and item.suffix.lower() == ".php"]
    tests = [item.relative_to(ROOT).as_posix() for item in files if "tests" in {part.lower() for part in item.parts} or "test" in item.stem.lower()]
    symbols = []
    for item in files:
        symbols.extend(php_symbols(item))

    extension_manifest = manifests.get("extension.json") if isinstance(manifests.get("extension.json"), dict) else {}
    registration_keys = set()
    for manifest in manifests.values():
        if not isinstance(manifest, dict):
            continue
        for key in ("slug", "key", "registration_key", "register_key", "name"):
            value = manifest.get(key)
            if isinstance(value, str) and value:
                registration_keys.add(f"{key}:{value}")

    return {
        "directory": path.name,
        "path": path.relative_to(ROOT).as_posix(),
        "file_count": len(files),
        "byte_count": sum(item.stat().st_size for item in files),
        "tree_fingerprint": tree_fingerprint(path),
        "manifest_files": sorted(manifests),
        "extension_manifest": extension_manifest,
        "manifest_name": extension_manifest.get("name") if isinstance(extension_manifest, dict) else None,
        "manifest_version": extension_manifest.get("version") if isinstance(extension_manifest, dict) else None,
        "manifest_description": extension_manifest.get("description") if isinstance(extension_manifest, dict) else None,
        "provider_files": providers,
        "provider_count": len(providers),
        "migration_files": migrations,
        "migration_count": len(migrations),
        "route_files": routes,
        "route_count": len(routes),
        "test_files": tests,
        "test_count": len(tests),
        "php_symbols": sorted(set(symbols)),
        "php_symbol_count": len(set(symbols)),
        "registration_keys": sorted(registration_keys),
    }


def extension_inventory() -> list[dict[str, Any]]:
    return [summarise_extension(path) for path in sorted(EXTENSIONS.iterdir(), key=lambda p: p.name.lower()) if path.is_dir()]


def duplicate_groups(items: list[dict[str, Any]]) -> dict[str, Any]:
    tree_groups: dict[str, list[str]] = defaultdict(list)
    symbol_groups: dict[str, list[str]] = defaultdict(list)
    migration_names: dict[str, list[str]] = defaultdict(list)
    manifest_names: dict[str, list[str]] = defaultdict(list)
    registration_keys: dict[str, list[str]] = defaultdict(list)

    for item in items:
        tree_groups[item["tree_fingerprint"]].append(item["directory"])
        for symbol in item["php_symbols"]:
            symbol_groups[symbol].append(item["directory"])
        for migration in item["migration_files"]:
            migration_names[Path(migration).name].append(item["directory"])
        if item["manifest_name"]:
            manifest_names[str(item["manifest_name"])].append(item["directory"])
        for key in item["registration_keys"]:
            registration_keys[key].append(item["directory"])

    return {
        "identical_tree_groups": {key: values for key, values in tree_groups.items() if len(values) > 1},
        "duplicate_php_symbols": {key: sorted(set(values)) for key, values in symbol_groups.items() if len(set(values)) > 1},
        "duplicate_migration_filenames": {key: sorted(set(values)) for key, values in migration_names.items() if len(set(values)) > 1},
        "duplicate_manifest_names": {key: sorted(set(values)) for key, values in manifest_names.items() if len(set(values)) > 1},
        "duplicate_registration_keys": {key: sorted(set(values)) for key, values in registration_keys.items() if len(set(values)) > 1},
    }


def reconcile_marketplace(items: list[dict[str, Any]], marketplace: dict[str, Any]) -> dict[str, Any]:
    by_dir = {item["directory"]: item for item in items}
    mapped = marketplace["providers"]
    mapped_directories = set()
    missing_provider_classes = []
    duplicate_provider_targets: dict[str, list[str]] = defaultdict(list)

    for slug, fqcn in mapped.items():
        parts = fqcn.split("\\")
        directory = parts[2] if len(parts) > 2 and parts[0:2] == ["App", "Extensions"] else None
        if directory:
            mapped_directories.add(directory)
        if fqcn.startswith("App\\"):
            provider_relative = "app/" + fqcn[len("App\\"):].replace("\\", "/") + ".php"
        else:
            provider_relative = fqcn.replace("\\", "/") + ".php"
        provider_path = ROOT / provider_relative
        if not provider_path.is_file():
            missing_provider_classes.append({"slug": slug, "provider": fqcn, "expected_path": provider_path.relative_to(ROOT).as_posix()})
        duplicate_provider_targets[fqcn].append(slug)

    filesystem_dirs = set(by_dir)
    return {
        "filesystem_extension_count": len(filesystem_dirs),
        "marketplace_mapping_count": len(mapped),
        "filesystem_not_mapped": sorted(filesystem_dirs - mapped_directories),
        "mapped_directory_missing": sorted(mapped_directories - filesystem_dirs),
        "missing_provider_classes": missing_provider_classes,
        "provider_class_used_by_multiple_slugs": {key: values for key, values in duplicate_provider_targets.items() if len(values) > 1},
    }


def findings(data: dict[str, Any]) -> list[dict[str, str]]:
    result: list[dict[str, str]] = []
    marketplace = data["marketplace"]
    lifecycle = data["lifecycle"]
    reconciliation = data["reconciliation"]
    duplicates = data["duplicates"]

    if marketplace["registers_all_mapped_providers"]:
        result.append({
            "classification": "confirmed activation risk",
            "finding": "When extension discovery is enabled, MarketplaceServiceProvider registers every mapped provider rather than only installed and qualified extensions.",
        })
    if marketplace["install_route_uses_get"] or marketplace["uninstall_route_uses_get"]:
        result.append({
            "classification": "confirmed unsafe lifecycle route",
            "finding": "Extension install/uninstall mutations are exposed as authenticated GET routes instead of CSRF-protected state-changing requests.",
        })
    install = lifecycle["install"]
    if install["zip_extract_to_direct"] and not install["zip_entry_validation_detected"]:
        result.append({
            "classification": "confirmed archive validation gap",
            "finding": "Remote extension ZIP content is extracted directly without detected per-entry traversal, symlink or destination validation.",
        })
    if install["zip_extract_to_direct"] and not install["signature_verification_detected"]:
        result.append({
            "classification": "confirmed supply-chain verification gap",
            "finding": "No extension archive signature or public-key verification was detected before extraction and execution.",
        })
    if install["chmod_0777"]:
        result.append({
            "classification": "confirmed permission hardening gap",
            "finding": "New extension directories are chmod 0777.",
        })
    if install["forced_migration"] and not install["transaction_detected"] and not install["rollback_path_detected"]:
        result.append({
            "classification": "confirmed upgrade rollback risk",
            "finding": "Installation runs forced migrations after extraction without a detected transactional install or rollback/restore path.",
        })
    if data["lifecycle"]["uninstall"]["database_rollback_detected"] is False:
        result.append({
            "classification": "confirmed uninstall residue risk",
            "finding": "Uninstall deletes extension files and invokes hooks but no database migration rollback was detected.",
        })
    if reconciliation["filesystem_not_mapped"]:
        result.append({
            "classification": "registry drift",
            "finding": f"{len(reconciliation['filesystem_not_mapped'])} extension directories are not represented in the static marketplace provider map.",
        })
    if reconciliation["missing_provider_classes"]:
        result.append({
            "classification": "registry drift",
            "finding": f"{len(reconciliation['missing_provider_classes'])} mapped provider classes are missing from expected paths.",
        })
    if duplicates["duplicate_php_symbols"]:
        result.append({
            "classification": "duplicate class risk",
            "finding": f"{len(duplicates['duplicate_php_symbols'])} PHP symbols occur in more than one extension directory.",
        })
    if duplicates["identical_tree_groups"]:
        result.append({
            "classification": "exact duplicate extension risk",
            "finding": f"{len(duplicates['identical_tree_groups'])} groups of extension directories have identical complete tree fingerprints.",
        })
    return result


def build_markdown(data: dict[str, Any]) -> str:
    items = data["extensions"]
    lines = [
        "# Extension Platform Runtime Inventory",
        "",
        f"Source commit: `{data['source_commit']}`",
        "",
        "This inventory separates files present on disk, marketplace mappings, manifests and lifecycle behaviour from verified installation, qualification and activation.",
        "",
        "## Totals",
        "",
        f"- Extension directories: **{len(items)}**",
        f"- Marketplace provider mappings: **{data['marketplace']['provider_count']}**",
        f"- Filesystem directories not mapped: **{len(data['reconciliation']['filesystem_not_mapped'])}**",
        f"- Missing mapped provider classes: **{len(data['reconciliation']['missing_provider_classes'])}**",
        f"- Duplicate PHP symbols across extension directories: **{len(data['duplicates']['duplicate_php_symbols'])}**",
        f"- Duplicate migration filenames across extension directories: **{len(data['duplicates']['duplicate_migration_filenames'])}**",
        "",
        "## Extension summary",
        "",
        "| Directory | Files | Manifest | Providers | Routes | Migrations | Tests | Marketplace mapped |",
        "|---|---:|---|---:|---:|---:|---:|---:|",
    ]
    mapped_dirs = {
        provider.split("\\")[2]
        for provider in data["marketplace"]["providers"].values()
        if provider.startswith("App\\Extensions\\") and len(provider.split("\\")) > 2
    }
    for item in items:
        lines.append(
            f"| `{item['directory']}` | {item['file_count']} | {', '.join(item['manifest_files']) or '—'} | "
            f"{item['provider_count']} | {item['route_count']} | {item['migration_count']} | {item['test_count']} | "
            f"{'yes' if item['directory'] in mapped_dirs else 'no'} |"
        )

    lines.extend(["", "## Marketplace activation model", ""])
    marketplace = data["marketplace"]
    lines.extend([
        f"- Discovery feature-gated: **{str(marketplace['extension_discovery_guarded']).lower()}**",
        f"- Registers all mapped providers when enabled: **{str(marketplace['registers_all_mapped_providers']).lower()}**",
        f"- Install mutation uses GET: **{str(marketplace['install_route_uses_get']).lower()}**",
        f"- Uninstall mutation uses GET: **{str(marketplace['uninstall_route_uses_get']).lower()}**",
        "",
        "## Lifecycle evidence",
        "",
    ])
    for phase, values in data["lifecycle"].items():
        lines.append(f"### {phase.title()}")
        lines.append("")
        for key, value in values.items():
            lines.append(f"- `{key}`: `{value}`")
        lines.append("")

    lines.extend(["## Findings", ""])
    for item in data["findings"]:
        lines.append(f"- **{item['classification']}:** {item['finding']}")

    lines.extend([
        "",
        "## Required platform rule",
        "",
        "- Files on disk do not make an extension installed, entitled, qualified or active.",
        "- Discovery must select only installed, enabled, compatible and qualified extensions.",
        "- Each active extension requires a validated manifest, unique slug/key/provider, dependency check, tenant/package gate and health result.",
        "- Install/uninstall must use authorised state-changing requests, verified archives, staging, rollback and auditable lifecycle records.",
        "- Providers, routes, migrations, permissions, menus and capability keys must register exactly once.",
        "- Extensions may add capabilities but may not replace host identity, tenancy, WorkCore authority, messaging authority or Vault.",
        "",
        "Full per-extension manifests, paths, symbols, duplicate groups and registry reconciliation are stored in the JSON inventory.",
        "",
    ])
    return "\n".join(lines)


def main() -> None:
    items = extension_inventory()
    marketplace = parse_marketplace_provider()
    data: dict[str, Any] = {
        "source_commit": git_sha(),
        "extensions": items,
        "marketplace": marketplace,
        "lifecycle": lifecycle_status(),
    }
    data["duplicates"] = duplicate_groups(items)
    data["reconciliation"] = reconcile_marketplace(items, marketplace)
    data["findings"] = findings(data)
    OUTPUT_JSON.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_JSON.write_text(json.dumps(data, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
    OUTPUT_MD.write_text(build_markdown(data), encoding="utf-8")
    print(f"Wrote {OUTPUT_JSON.relative_to(ROOT)}")
    print(f"Wrote {OUTPUT_MD.relative_to(ROOT)}")


if __name__ == "__main__":
    main()
