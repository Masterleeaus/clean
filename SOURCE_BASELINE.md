# Source Baseline

## Extracted working sources

| Component | Source archive | Extracted files | Intended role |
|---|---|---:|---|
| App base | `MagicAI-v10.91-WORKCORE-MERGED.zip` | 6,902 | Canonical Laravel/MagicAI + WorkCore host |
| Interaction Engine | Phase 15 cumulative, overlaid with host-boundary repair | 404 | Canonical server-side Interaction/Wizard package candidate |
| Chatbot PWA | `Titan-Zero-Chatbot-PWA-PASS12-HOST-BOUNDARY-FIXED(1).zip` | 1,542 | Device-first UI and offline runtime |
| Extension SDK | `TitanZero-Extension-SDK-v2.0.0(1).zip` | 68 | Extension registration and contribution contracts |

## Additional versions retained for comparison

- Interaction Engine.zip
- InteractionEngine Wizard cumulative Phase 8
- InteractionEngine Wizard cumulative Phase 9 / 80 core engines
- Phase 10 Local Intelligence
- Phase 11 Cognitive Events
- Phase 14 Authority Controls
- Phase 15 Calibrated Confidence
- Pass 1 Host Boundary Fixed
- TitanZero Phase 10 Fixed Complete
- Chatbot PWA base, Pass 11 and Pass 12
- AI, Base App, Marketing/Creative and Titan BOS extension packs

## Merge rule

No archive is treated as globally newer solely from its filename. Canonical selection is made per file and subsystem using content, references, tests, provider wiring and architectural authority.
