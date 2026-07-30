# Integration Layer

The Integration layer abstracts external systems through versioned provider manifests and adapters.

Providers expose:

- capabilities;
- authentication mode and secret-storage authority;
- operations and mappings;
- schemas;
- limits and rate constraints;
- failure modes and retry policy;
- health checks;
- required permissions.

Initial provider families include GitHub, Build Web Apps, MiniUp, Netlify, CLI and Docker. Workflows reference generic actions; adapters translate them into provider-specific operations.

Provider presence does not imply authentication, entitlement, health or permission. Secrets remain in Titan Vault or an approved host secret store.
