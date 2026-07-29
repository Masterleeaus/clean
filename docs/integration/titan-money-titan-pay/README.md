# Titan Money + Titan Pay Integration Workspace

This directory holds evidence and decisions for the staged v0.5.0 integration.

## Required sequence

1. Inventory the clean host and current WorkCore/Chatbot implementation.
2. Inventory the staged source pack.
3. Produce a path-collision ledger.
4. Produce the canonical authority and ownership matrix.
5. Reconcile Laravel, Composer, Node, queue, scheduler and storage assumptions.
6. Register security regressions before moving donor code into runtime.
7. Implement cumulative passes from the root upgrade plan.

## Evidence standard

Every finding must identify:

- exact source path;
- current authority;
- tenant scope;
- incoming and outgoing interfaces;
- disposition;
- tests required before integration.

## Staged source

`source-packs/titan-money-titan-pay-v0.5.0/` is not part of the runtime. Do not autoload it or copy its root application shell over the clean host.

## Final runtime targets

- `app/Domains/TitanMoney/`
- `app/Domains/TitanPay/`
- narrow adapters into the existing `app/Domains/WorkCore/`
- governed tools in the existing Titan Zero/AI registries
- customer delivery through the existing Chatbot/Channels layer
