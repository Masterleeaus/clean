# Onboarding branch artifacts

## Interaction Engine cumulative archive

The verified reconciled archive is hosted through MiniUp because the GitHub connector cannot directly ingest the local ZIP binary.

Archive landing page:

https://titan-zero-onboarding-artifacts.miniup.app

Archive file:

`TitanZero-InteractionEngine-FULL-CUMULATIVE-RECONCILED-PASS2-DELTA.zip`

Size: `582951` bytes

SHA-256:

`fe89e2937b72b0d0d387ffda8fc2afed08e6af85930a0c322ac59ffa18b3650f`

## Intended use

1. Download and verify the archive.
2. Extract it into the onboarding branch workspace.
3. Inventory source, configuration, migrations, routes, tests and compatibility bridges.
4. Merge only canonical Interaction Engine code.
5. Preserve WorkCore as the sole operational write authority.
