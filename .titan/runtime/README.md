# Runtime

Runtime holds transient execution state:

- active plans;
- events;
- artifacts;
- results;
- execution logs;
- temporary files;
- worker and planner state.

Runtime is reconstructable from accepted tasks, repository state and durable records. Permanent decisions, lessons, accepted policies and architectural memory must be promoted out of Runtime immediately.

Do not commit credentials, private user data, large generated binaries or uncontrolled transient logs. Runtime retention, cleanup and redaction policies must fail safely.

No persistent task queue or event bus is claimed operational in the bootstrap pass.
