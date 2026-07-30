# System-Generated Documentation

Nothing under this directory is manually edited after its generator exists.

## Generated areas

- `architecture/`
- `api/`
- `domains/`
- `modules/`
- `services/`
- `routes/`
- `events/`
- `commands/`
- `capabilities/`
- `workflows/`
- `validators/`
- `dependencies/`
- `graphs/`
- `inventories/`
- `reports/`

Every generated file must identify:

```yaml
generated: true
generator: titan-docs
generator_version: 1.0.0
source_commit: <git-sha>
generated_at: <timestamp>
inputs: []
schema_version: 1
freshness: current | stale | failed
```

## Failure behaviour

A failed generation preserves the last valid output and marks it stale. It must not replace valid data with an empty, partial or lower-confidence result.

## Current state

Generators are not yet operational. This README defines the contract only. Generated architecture, API, graph, dashboard and inventory claims must not be made until the corresponding generator and validation path exist.
