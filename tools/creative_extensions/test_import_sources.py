from __future__ import annotations

import zipfile
from pathlib import Path

import pytest

from import_sources import extract_archive


def make_zip(path: Path, members: dict[str, bytes]) -> None:
    with zipfile.ZipFile(path, "w") as archive:
        for name, content in members.items():
            archive.writestr(name, content)


def test_normalises_windows_paths_and_hashes_files(tmp_path: Path) -> None:
    archive = tmp_path / "sample.zip"
    make_zip(
        archive,
        {
            "System\\Provider.php": b"<?php",
            "extension.json": b"{}",
        },
    )

    result = extract_archive(archive, tmp_path / "out", "sample")

    assert result.file_count == 2
    assert (tmp_path / "out" / "System" / "Provider.php").read_bytes() == b"<?php"
    assert [item.path for item in result.files] == [
        "System/Provider.php",
        "extension.json",
    ]


def test_rejects_path_traversal(tmp_path: Path) -> None:
    archive = tmp_path / "bad.zip"
    make_zip(archive, {"../escape.php": b"bad"})

    with pytest.raises(ValueError, match="unsafe archive path"):
        extract_archive(archive, tmp_path / "out", "bad")


def test_rejects_absolute_path(tmp_path: Path) -> None:
    archive = tmp_path / "bad.zip"
    make_zip(archive, {"/tmp/escape.php": b"bad"})

    with pytest.raises(ValueError, match="unsafe absolute"):
        extract_archive(archive, tmp_path / "out", "bad")
