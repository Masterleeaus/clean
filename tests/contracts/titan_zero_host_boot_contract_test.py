#!/usr/bin/env python3
from __future__ import annotations

import json
import re
import unittest
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]


class HostBootContractTest(unittest.TestCase):
    def test_interaction_engine_is_registered_once(self) -> None:
        composer = json.loads((ROOT / 'composer.json').read_text())
        repositories = composer.get('repositories', [])
        self.assertTrue(
            any(
                repo.get('type') == 'path'
                and repo.get('url') == './packages/titanzero/interaction-engine'
                for repo in repositories
            ),
            'root composer.json must declare the local Interaction Engine path repository',
        )
        self.assertEqual('@dev', composer.get('require', {}).get('titanzero/interaction-engine'))

        package = json.loads(
            (ROOT / 'packages/titanzero/interaction-engine/composer.json').read_text()
        )
        providers = package.get('extra', {}).get('laravel', {}).get('providers', [])
        self.assertEqual(
            [],
            providers,
            'the merged host uses explicit provider registration, not package discovery',
        )

        app_config = (ROOT / 'config/app.php').read_text()
        provider = 'TitanZero\\Interaction\\Providers\\InteractionServiceProvider::class'
        self.assertEqual(
            1,
            app_config.count(provider),
            'Interaction Engine provider must be registered exactly once',
        )

    def test_interaction_engine_is_locked(self) -> None:
        lock = json.loads((ROOT / 'composer.lock').read_text())
        package_names = {
            package.get('name')
            for section in ('packages', 'packages-dev')
            for package in lock.get(section, [])
        }
        self.assertIn(
            'titanzero/interaction-engine',
            package_names,
            'composer.lock must include the local Interaction Engine package for reproducible installs',
        )

    def test_interaction_migrations_create_each_table_once(self) -> None:
        migration_roots = [
            ROOT / 'packages/titanzero/interaction-engine/src/Migrations',
            ROOT / 'packages/titanzero/interaction-engine/database/migrations',
        ]
        tables: dict[str, list[str]] = defaultdict(list)
        for migration_root in migration_roots:
            for path in migration_root.glob('*.php'):
                content = path.read_text(errors='ignore')
                for table in re.findall(r"Schema::create\(['\"]([^'\"]+)", content):
                    tables[table].append(str(path.relative_to(ROOT)))

        duplicates = {table: paths for table, paths in tables.items() if len(paths) > 1}
        self.assertEqual(
            {},
            duplicates,
            f'duplicate Interaction Engine table creators found: {duplicates}',
        )
        self.assertGreaterEqual(len(tables), 10, 'expected the cumulative Interaction Engine schema')


if __name__ == '__main__':
    unittest.main()
