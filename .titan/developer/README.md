# Developer Experience Layer

This layer contains executable developer-experience assets rather than human documentation.

## Planned areas

- generators
- scaffolds
- commands
- hooks
- linting
- templates
- fixtures
- environments

Human-readable tutorials, playbooks, examples and conventions belong in `/.titan/documentation/developer/` or canonical `/docs` sections.

Every executable tool must declare inputs, outputs, permissions, failure behaviour, idempotency and validation. Tools may not bypass repository authority, branch, security or release policy.

No generators or hooks are installed by this bootstrap pass.
