#!/usr/bin/env python3
"""Securely import Titan Zero creative-extension ZIP archives.

The supplied extension archives may contain Windows path separators and UTF-8 BOM
manifests. This importer normalises paths, rejects traversal/symlink entries,
extracts into deterministic extension roots, and writes a provenance inventory.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import shutil
import stat
import zipfile
from dataclasses import asdict, dataclass
from pathlib import Path, PurePosixPath
from typing import Iterable


@dataclass(frozen=True)
class ImportedFile:
    path: str
    size: int
    sha256: str


@dataclass(frozen=True)
class ImportedExtension:
    name: str
    archive: str
    archive_sha256: str
    file_count: int
    files: list[ImportedFile]


def sha256_file(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def normalise_member(raw_name: str) -> PurePosixPath:
    path = PurePosixPath(raw_name.replace("\\", "/"))
    if path.is_absolute() or not path.parts:
        raise ValueError(f"unsafe absolute or empty archive path: {raw_name!r}")
    if any(part in {"", ".", ".."} for part in path.parts):
        raise ValueError(f"unsafe archive path segment: {raw_name!r}")
    return path


def is_symlink(info: zipfile.ZipInfo) -> bool:
    unix_mode = info.external_attr >> 16
    return stat.S_ISLNK(unix_mode)


def extract_archive(archive: Path, destination: Path, name: str) -> ImportedExtension:
    if destination.exists():
        shutil.rmtree(destination)
    destination.mkdir(parents=True, exist_ok=True)

    imported: list[ImportedFile] = []

    with zipfile.ZipFile(archive) as package:
        for info in package.infolist():
            member = normalise_member(info.filename)
            if is_symlink(info):
                raise ValueError(f"symlink entries are not permitted: {info.filename!r}")

            target = destination.joinpath(*member.parts)
            resolved = target.resolve()
            if destination.resolve() not in resolved.parents and resolved != destination.resolve():
                raise ValueError(f"archive member escapes destination: {info.filename!r}")

            if info.is_dir():
                target.mkdir(parents=True, exist_ok=True)
                continue

            target.parent.mkdir(parents=True, exist_ok=True)
            with package.open(info, "r") as source, target.open("wb") as output:
                shutil.copyfileobj(source, output)

            imported.append(
                ImportedFile(
                    path=target.relative_to(destination).as_posix(),
                    size=target.stat().st_size,
                    sha256=sha256_file(target),
                )
            )

    imported.sort(key=lambda item: item.path)
    return ImportedExtension(
        name=name,
        archive=archive.name,
        archive_sha256=sha256_file(archive),
        file_count=len(imported),
        files=imported,
    )


def parse_source(value: str) -> tuple[str, Path]:
    try:
        name, archive = value.split("=", 1)
    except ValueError as exc:
        raise argparse.ArgumentTypeError("source must use NAME=/path/to/archive.zip") from exc
    if not name.strip():
        raise argparse.ArgumentTypeError("source name cannot be empty")
    path = Path(archive).expanduser().resolve()
    if not path.is_file():
        raise argparse.ArgumentTypeError(f"archive does not exist: {path}")
    return name.strip(), path


def import_sources(sources: Iterable[tuple[str, Path]], output: Path) -> list[ImportedExtension]:
    source_root = output / "extensions" / "source"
    source_root.mkdir(parents=True, exist_ok=True)
    results = [extract_archive(archive, source_root / name, name) for name, archive in sources]
    results.sort(key=lambda item: item.name)
    return results


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--source",
        action="append",
        type=parse_source,
        required=True,
        help="Extension source in NAME=/path/to/archive.zip form; repeat for each archive.",
    )
    parser.add_argument("--output", type=Path, required=True, help="Workspace root.")
    args = parser.parse_args()

    output = args.output.expanduser().resolve()
    output.mkdir(parents=True, exist_ok=True)
    imported = import_sources(args.source, output)

    audit_dir = output / "docs" / "creative-audit"
    audit_dir.mkdir(parents=True, exist_ok=True)
    manifest_path = audit_dir / "source-inventory.json"
    manifest_path.write_text(
        json.dumps({"extensions": [asdict(item) for item in imported]}, indent=2) + "\n",
        encoding="utf-8",
    )

    total = sum(item.file_count for item in imported)
    print(f"Imported {len(imported)} extensions and {total} files")
    print(f"Inventory: {manifest_path}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
