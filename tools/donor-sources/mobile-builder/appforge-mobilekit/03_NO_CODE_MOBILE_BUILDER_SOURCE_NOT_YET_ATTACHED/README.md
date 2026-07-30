# No-Code Mobile Builder Source Not Yet Attached

The exact no-code mobile-builder ZIP has not been supplied to the active sandbox.

No code is included in this folder. When supplied, extract candidates into:

- `reuse_candidates/editor_canvas/`
- `reuse_candidates/component_tree/`
- `reuse_candidates/property_inspector/`
- `reuse_candidates/document_schema/`
- `adapt_required/data_binding/`
- `adapt_required/publishing/`
- `rejected/runtime_eval_or_arbitrary_code/`

Required review gates:

1. No `eval`, dynamic JavaScript execution, arbitrary SQL, or direct database credentials.
2. No donor authentication, billing, tenancy, or permission authority.
3. Persist a versioned Titan app specification rather than arbitrary HTML/JS.
4. Bind only registered Titan components and governed WorkCore actions.
