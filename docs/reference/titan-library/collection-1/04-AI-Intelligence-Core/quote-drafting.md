# Second AI Feature — Quote / Service Description Drafting

## What was built

An AI-assisted drafting layer for generating customer-facing service-business
quote descriptions from structured job inputs.
No vendor files were modified and no Filament UI components were added.

### New files

| File | Purpose |
|---|---|
| `app/Services/AI/QuoteDraftInput.php` | Typed read-only value-object for all quote inputs (service type, property, scope, frequency, tone, …). |
| `app/Services/AI/QuoteDraftResult.php` | Typed read-only value-object for the structured AI output (summary, description, included-items list, assumptions). Parses the AI's labelled-section response. |
| `app/Services/AI/Agents/QuoteDraftAgent.php` | laravel/ai Agent that holds the copywriter system prompt and builds the user prompt from a `QuoteDraftInput`. |
| `app/Console/Commands/AiDraftQuoteCommand.php` | Artisan command entry-point (`ai:draft-quote`). |
| `docs/ai/quote-drafting.md` | This document. |

### Modified files

| File | Change |
|---|---|
| `app/Services/AI/AiTextService.php` | Added `draftQuote(QuoteDraftInput, ?provider, ?model): QuoteDraftResult` method. |

---

## Packages used

| Package | Role |
|---|---|
| `laravel/ai` | Text-generation via any configured provider. |
| `prism-php/prism` | Underlying provider gateway used by laravel/ai. |

laravel-rag is **not used** here — this feature works directly with `laravel/ai`
and does not require a vector store.

---

## How to run it

### 1. Configure an AI provider key

```bash
# .env
OPENAI_API_KEY=sk-...
# — or —
ANTHROPIC_API_KEY=sk-ant-...
# — or — (local, free)
OLLAMA_URL=http://localhost:11434
```

### 2. Run the artisan command

```bash
# Minimal — just a service type
php artisan ai:draft-quote --service="End-of-lease clean"

# Typical — full details
php artisan ai:draft-quote \
  --service="End-of-lease clean" \
  --property="3-bedroom apartment, 2 bathrooms" \
  --scope="Full interior clean including oven, range hood, windows, and bathrooms" \
  --frequency="One-off" \
  --requirements="Please use fragrance-free products" \
  --exclusions="External windows above ground floor, garage" \
  --tone=professional

# Friendly tone (great for residential customers)
php artisan ai:draft-quote \
  --service="Regular home clean" \
  --property="4-bed family home" \
  --frequency="Fortnightly" \
  --tone=friendly

# Override provider/model
php artisan ai:draft-quote --service="Office deep clean" \
  --provider=anthropic --model=claude-3-5-haiku-20241022

# Local Ollama model (no API key needed)
php artisan ai:draft-quote --service="Carpet steam clean" \
  --provider=ollama --model=llama3

# Show raw AI response alongside parsed output (useful for debugging)
php artisan ai:draft-quote --service="Builder's clean" --raw
```

### 3. Use the service from code

```php
use App\Services\AI\AiTextService;
use App\Services\AI\QuoteDraftInput;

$input = new QuoteDraftInput(
    serviceType:         'End-of-lease clean',
    propertyType:        '3-bedroom apartment',
    scopeOfWork:         'Full interior including oven and windows',
    frequency:           'One-off',
    specialRequirements: 'Fragrance-free products only',
    exclusions:          'External windows above ground floor',
    tone:                'professional',
);

$result = app(AiTextService::class)->draftQuote($input);

if ($result->isUsable()) {
    echo $result->summary;        // One-sentence overview
    echo $result->description;    // Customer-facing paragraph(s)
    $result->includedItems;       // list<string> of bullet items
    $result->assumptions;         // list<string> of exclusions/assumptions
}
```

### 4. Example output

```
SUMMARY:
Professional end-of-lease cleaning service for a 3-bedroom apartment, ensuring
the property is returned to its original condition.

DESCRIPTION:
This one-off deep-clean service covers all interior areas of the apartment,
including kitchen appliances, bathrooms, and windows. Our team uses
fragrance-free, eco-friendly products throughout. All surfaces, fixtures, and
fittings will be cleaned to a bond-return standard, giving you confidence on
inspection day.

INCLUDED WORK:
  • Full kitchen clean — benches, cupboards inside/out, sink, splashback
  • Oven and range hood degreased and cleaned
  • Bathroom and ensuite — tiles, shower screens, vanities, toilets sanitised
  • All internal windows and tracks cleaned
  • Floors vacuumed and mopped throughout
  • Skirtings, light switches, and door handles wiped down

ASSUMPTIONS / EXCLUSIONS:
  • External windows above ground floor are excluded
  • Garage is excluded from scope
  • Fragrance-free products will be used throughout
```

---

## How Filament v4 incompatibility is avoided

This feature is entirely CLI/service-layer based:
- No Filament resources were created.
- No `RagPlugin` was registered.
- The `AdminPanelProvider` was not modified.

Future Filament UI integration (e.g., a "Draft Quote Description" action on the
Estimate/Quote resource) should be built as a native Filament v4 `Action` that
calls `AiTextService::draftQuote()` — never by wrapping package UI classes.

---

## Tone options

| Value | Use case |
|---|---|
| `professional` | Default. Suitable for most B2B and residential quotes. |
| `friendly` | Warmer language for residential/consumer customers. |
| `formal` | Strict language for government, body corporate, or tender submissions. |

---

## Input fields reference

| Field | CLI option | Description |
|---|---|---|
| Service type | `--service` | **Required.** e.g. "End-of-lease clean" |
| Property type | `--property` | e.g. "3-bedroom apartment, 2 bathrooms" |
| Scope of work | `--scope` | Free-text description of what is included |
| Frequency | `--frequency` | e.g. "One-off", "Weekly", "Fortnightly" |
| Special requirements | `--requirements` | Customer notes or specific instructions |
| Exclusions | `--exclusions` | Items explicitly out of scope |
| Tone | `--tone` | `professional` (default), `friendly`, or `formal` |

---

## Recommended next features

1. **Filament v4 Action** — a "Draft Description" button on the Estimate resource
   that calls `AiTextService::draftQuote()` and pre-fills the description field.
2. **Support message classifier** — classify incoming ticket/issue messages into
   service categories using a `ClassifyMessageAgent`.
3. **RAG-augmented quoting** — index past quotes/proposals with `rag:index` and
   retrieve similar past work to improve the draft's accuracy.
4. **Queue-backed drafting** — use `QuoteDraftAgent::make()->queue(...)` so
   large batches of quotes can be drafted asynchronously.
