# Shared-file lock requests

No shared-file lock is currently held by this branch.

The first implementation increment is deliberately domain-local and unregistered.

Before activating Titan Train contributions through staged providers or the canonical Interaction Engine registry, request coordinator approval using:

```text
LOCK REQUEST
File: config/titan-zero.php and/or canonical Interaction Engine contribution registry
Reason: activate one Titan Train interaction-definition contributor behind the existing Interaction Engine feature flag
Expected changes: add one feature-gated provider/contributor path; no duplicate runtime or registry
Dependent branches: reconcile/provider-gating, reconcile/product-shell
Expected release time: coordinator-controlled
```

Do not edit `config/app.php`, global provider registries, extension registries, global navigation or workflow files on this branch without approval.
