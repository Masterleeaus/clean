# Native Chatbot PWA integration

Titan Train is registered twice for two different purposes:

1. `TitanSuiteTemplates/titan-train/config.json` makes it discoverable through the server-side Titan app registry.
2. `TemplateSchemas/titan-train.json` defines the four-view Chatbot shell.

The workspace is rendered by `titan-train-workspace.js` before the generic operational renderer starts. It marks the Titan Train workspace element as claimed, so the generic fourteen-app renderer does not replace it.

## Online authority

The workspace reads `/api/v1/titan-train/pwa/bootstrap` and sends all mutations to Titan Train. It never writes learning data to the Chatbot device database or offline outbox.

## Channel authority

Training channel links come from Titan Train but conversation ownership stays with Titan Channels. Selecting a channel dispatches:

- `chatbot:team-channel-open-requested`
- `titan-train:channel-opened`

The event contains the canonical channel link and declares `authority: titan-channels`.
