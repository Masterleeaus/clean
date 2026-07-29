#!/usr/bin/env python3
from __future__ import annotations

import json
import re
import unittest
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]


def table_creators(migration_roots: list[Path]) -> dict[str, list[str]]:
    tables: dict[str, list[str]] = defaultdict(list)
    for migration_root in migration_roots:
        for path in migration_root.glob('*.php'):
            content = path.read_text(errors='ignore')
            for table in re.findall(r"Schema::create\(['\"]([^'\"]+)", content):
                tables[table].append(str(path.relative_to(ROOT)))
    return tables


def constructor_body(source: str) -> str:
    match = re.search(
        r'public function __construct\([^)]*\)\s*\{(?P<body>.*?)\n\s*\}',
        source,
        flags=re.DOTALL,
    )
    return match.group('body') if match else ''


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
        tables = table_creators([
            ROOT / 'packages/titanzero/interaction-engine/src/Migrations',
            ROOT / 'packages/titanzero/interaction-engine/database/migrations',
        ])
        duplicates = {table: paths for table, paths in tables.items() if len(paths) > 1}
        self.assertEqual(
            {},
            duplicates,
            f'duplicate Interaction Engine table creators found: {duplicates}',
        )
        self.assertGreaterEqual(len(tables), 10, 'expected the cumulative Interaction Engine schema')

    def test_queued_commands_migration_declares_each_timestamp_once(self) -> None:
        migration = (
            ROOT
            / 'packages/titanzero/interaction-engine/src/Migrations/'
            '2026_08_02_000000_create_queued_commands_table.php'
        ).read_text()
        self.assertEqual(1, migration.count("timestamp('created_at')"))
        self.assertEqual(1, migration.count("timestamp('updated_at')"))
        self.assertNotIn('$table->timestamps()', migration)

    def test_chatbot_migrations_create_each_table_once(self) -> None:
        tables = table_creators([ROOT / 'app/Extensions/Chatbot/database/migrations'])
        duplicates = {table: paths for table, paths in tables.items() if len(paths) > 1}
        self.assertEqual(
            {},
            duplicates,
            f'duplicate Chatbot table creators found: {duplicates}',
        )

    def test_workcore_fulltext_migration_is_sqlite_safe(self) -> None:
        migration = (
            ROOT
            / 'app/Domains/WorkCore/Database/Migrations/'
            '2026_07_23_120058_create_tz_ai_knowledge_tables.php'
        ).read_text()
        self.assertIn("DB::connection()->getDriverName() !== 'sqlite'", migration)
        self.assertIn("$table->fullText('content', 'ai_kchunk_content_ft')", migration)

    def test_controller_constructors_do_not_abort_route_inspection(self) -> None:
        controller = (ROOT / 'app/Http/Controllers/Team/TeamController.php').read_text()
        body = constructor_body(controller)
        self.assertNotIn('abort_if(', body)
        self.assertIn('$this->middleware(', body)

    def test_tts_controller_constructor_has_no_database_side_effects(self) -> None:
        controller = (ROOT / 'app/Http/Controllers/TTSController.php').read_text()
        body = constructor_body(controller)
        for forbidden in ('Setting::getCache', 'SettingTwo::getCache', 'OpenAIGenerator::where', '->first('):
            self.assertNotIn(forbidden, body)
        self.assertIn('private function settings()', controller)
        self.assertIn('private function settingsTwo()', controller)
        self.assertIn('private function voiceoverGenerator()', controller)


if __name__ == '__main__':
    unittest.main()
