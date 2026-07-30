#!/usr/bin/env python3

from __future__ import annotations

import json
from collections import Counter
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
SOURCE = ROOT / 'docs/inventory/EXTENSION_PLATFORM_INVENTORY.json'
OUTPUT = ROOT / 'docs/inventory/EXTENSION_PLATFORM_GAPS.md'


def main() -> None:
    data = json.loads(SOURCE.read_text(encoding='utf-8'))
    reconciliation = data['reconciliation']
    duplicates = data['duplicates']
    extensions = data['extensions']

    duplicate_symbol_counts = Counter()
    for symbol, dirs in duplicates.get('duplicate_php_symbols', {}).items():
        key = ' ↔ '.join(dirs)
        duplicate_symbol_counts[key] += 1

    duplicate_migration_counts = Counter()
    for filename, dirs in duplicates.get('duplicate_migration_filenames', {}).items():
        key = ' ↔ '.join(dirs)
        duplicate_migration_counts[key] += 1

    no_tests = [item['directory'] for item in extensions if item['test_count'] == 0]
    no_manifest = [item['directory'] for item in extensions if not item['manifest_files']]
    invalid_manifests = [
        item['directory']
        for item in extensions
        if isinstance(item.get('extension_manifest'), dict) and item['extension_manifest'].get('invalid')
    ]

    lines = [
        '# Extension Platform Registry Gaps',
        '',
        f"Source inventory commit: `{data['source_commit']}`",
        '',
        '## Filesystem extensions not mapped by Marketplace',
        '',
    ]
    for directory in reconciliation.get('filesystem_not_mapped', []):
        lines.append(f'- `{directory}`')
    if not reconciliation.get('filesystem_not_mapped'):
        lines.append('- None')

    lines.extend(['', '## Marketplace mappings with missing provider classes', ''])
    for item in reconciliation.get('missing_provider_classes', []):
        lines.append(f"- `{item['slug']}` → `{item['provider']}` — expected `{item['expected_path']}`")
    if not reconciliation.get('missing_provider_classes'):
        lines.append('- None')

    lines.extend(['', '## Provider classes used by multiple slugs', ''])
    for provider, slugs in reconciliation.get('provider_class_used_by_multiple_slugs', {}).items():
        lines.append(f"- `{provider}`: {', '.join(f'`{slug}`' for slug in slugs)}")
    if not reconciliation.get('provider_class_used_by_multiple_slugs'):
        lines.append('- None')

    lines.extend(['', '## Dominant duplicate PHP-symbol directory pairs', ''])
    for pair, count in duplicate_symbol_counts.most_common(20):
        lines.append(f'- `{pair}`: **{count}** duplicated symbols')
    if not duplicate_symbol_counts:
        lines.append('- None')

    lines.extend(['', '## Dominant duplicate migration-filename directory pairs', ''])
    for pair, count in duplicate_migration_counts.most_common(20):
        lines.append(f'- `{pair}`: **{count}** duplicate migration filenames')
    if not duplicate_migration_counts:
        lines.append('- None')

    lines.extend([
        '',
        '## Manifest and test coverage',
        '',
        f'- Extension directories without any recognised manifest: **{len(no_manifest)}**',
        f'- Invalid `extension.json` documents: **{len(invalid_manifests)}**',
        f'- Extension directories with no detected test files: **{len(no_tests)}** of **{len(extensions)}**',
        '',
        '### No detected tests',
        '',
    ])
    lines.extend(f'- `{name}`' for name in no_tests)

    lines.extend([
        '',
        '## Interpretation',
        '',
        '- A missing provider mapping may mean stale registry code, removed extension source, a renamed directory or an incomplete import.',
        '- A filesystem directory not in the marketplace map is not automatically active; it requires explicit classification.',
        '- Duplicate symbols and migrations must be traced to copied extensions, compatibility layers or genuinely shared code before deletion.',
        '- Zero detected tests does not prove an extension is broken, but it prevents qualification as production-ready without additional evidence.',
        '',
    ])
    OUTPUT.write_text('\n'.join(lines), encoding='utf-8')


if __name__ == '__main__':
    main()
