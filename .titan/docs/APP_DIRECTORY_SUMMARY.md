# Application Directory Summary

## Release root

`Titan-Zero-Meetup-WorkCore-Integrated-v0.7.0/`

The complete file-by-file hierarchy is available in [`APP_DIRECTORY_TREE.txt`](APP_DIRECTORY_TREE.txt). The inventory excludes Git metadata, Composer `vendor`, npm `node_modules`, secrets, logs and generated framework caches.

## Top-level directory

```text
Titan-Zero-Meetup-WorkCore-Integrated-v0.7.0/
├── .github/                   Connected CI workflow
├── app/                       Laravel runtime and Titan/WorkCore source
├── bin/                       Build, preflight and verification commands
├── bootstrap/                 Laravel bootstrap and provider registration
├── config/                    Laravel and Titan configuration
├── database/                  Migrations, factories and seeders
├── docker/                    PHP/Composer/Node verification image
├── docs/                      Architecture, integration and release records
├── public/                    Public entry point and static assets
├── resources/                 Views, source JavaScript, CSS and WorkCore manifests
├── routes/                    Web, API, console, channel and Titan routes
├── storage/                   Runtime storage skeleton only
├── tests/                     Framework and dependency-free contract suites
├── tools/                     Release, namespace, migration and route verifiers
├── docker-compose.yml
├── DEPLOYMENT.md
├── APP_DIRECTORY_SUMMARY.md
├── APP_DIRECTORY_TREE.txt
├── BUILD_REPORT.md
└── README.md
```

## Runtime ownership map

```text
app/
├── Domains/WorkCore/          Operational business truth and governed domain modules
├── Extensions/
│   └── TitanMapsIntelligence/ Optional provider/business discovery extension
├── Http/                      Authenticated controllers, middleware and requests
├── Models/                    Meetup host and company models
├── Providers/                 Laravel application providers
├── Services/                  Meetup host services
└── Titan/
    ├── AI/                    Titan Zero orchestration and company AI configuration
    ├── Audit/                 Immutable host audit
    ├── Capabilities/          Governed capability registry
    ├── Creative/              Native Creative and Marketing runtime
    ├── Extensions/            Extension registry and compatibility checks
    ├── Intelligence/          Workspace, memory, skills, agents, connectors and voice
    ├── Maps/                  Host adapters for Maps Intelligence
    ├── Permissions/           Delegated permission enforcement
    ├── Tenancy/               Active-company context
    ├── Vault/                 Encrypted credentials and secret references
    └── WorkCore/              Governed WorkCore host adapters
```

## Deployment surfaces

```text
bin/
├── titan-build
├── titan-preflight
├── titan-verify-connected
└── titan-verify-offline

docker/
└── php/
    ├── Dockerfile
    └── entrypoint.sh

tools/
├── titan_migration_order.php
├── titan_namespace_scan.php
├── titan_route_provider_scan.php
└── titan_verify.php
```

## WorkCore modules (24)

Assets, Assurance, Attendance, CRM, Catalogue, Dispatch, Documents, Finance, Fleet, Forms, Inventory, KnowledgeBase, NDIS, Operations, Payments, Premises, Repairs, Reviews, Rosters, Scheduling, Supply, Support, TrustAccounting, Workforce

## Titan host subsystems (11)

AI, Audit, Capabilities, Creative, Extensions, Intelligence, Maps, Permissions, Tenancy, Vault, WorkCore

## Inventory counts

- Packaged source files: **1,128**
- Directories represented: **323**
- PHP files: **990**
- JavaScript files: **14**
- Blade templates: **18**
- Laravel migration files: **61**
- WorkCore module directories: **24**
- Titan host subsystem directories: **11**

## Files by top-level area

| Area | Files |
|---|---:|
| `app` | 840 |
| `database` | 66 |
| `tests` | 64 |
| `resources` | 38 |
| `public` | 35 |
| `docs` | 24 |
| `config` | 17 |
| `storage` | 6 |
| `routes` | 5 |
| `bin` | 4 |
| `tools` | 4 |
| `bootstrap` | 3 |
| `docker` | 2 |
| `.github` | 1 |
| Other root files | 19 |

## Packaging exclusions

The release ZIP does not contain:

- `.git` metadata or worktrees
- Composer `vendor`
- npm `node_modules`
- `.env` or provider credentials
- application logs
- framework cache/session/view output
- SQLite verification databases
- donor archives or donor source folders
- compiled donor executables, DLLs or PDB files
