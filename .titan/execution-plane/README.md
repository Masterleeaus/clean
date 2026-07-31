# Execution Plane

The Execution Plane contains worker agents, manifests, tasks, queues, mailboxes and execution state.

Worker agents implement approved plans through declared providers and actions. They do not redefine architecture or bypass Kernel policy.

Each persistent agent has:

- a unique ID and role;
- declared permissions and prohibited actions;
- provider and capability references;
- current task and execution state;
- mailbox subscription;
- engineering journal;
- evidence-based trust record.

The v1.0 bootstrap contains contracts and onboarding only. It does not claim an operational scheduler or autonomous worker fleet.
