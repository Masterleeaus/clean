# First AI Feature — Text Summarisation

## What was built

A minimal, production-safe AI summarisation layer sitting entirely inside the application (`app/`).
No vendor files were modified and no incompatible Filament sidebar integrations were enabled.

### New files

| File | Purpose |
|---|---|
| `config/ai.php` | Published laravel/ai configuration (providers, keys, caching). |
| `app/Services/AI/AiTextService.php` | App-level service wrapping `laravel/ai`. Handles missing keys gracefully and logs errors instead of crashing. |
| `app/Services/AI/Agents/SummarizeAgent.php` | A typed `laravel/ai` Agent class for text summarisation. Usable from any part of the application. |
| `app/Console/Commands/AiSummarizeCommand.php` | Artisan command entry-point (`ai:summarize`). |
| `stubs/agent.stub` | Laravel/ai stub (published with config). |
| `docs/ai/first-ai-feature.md` | This document. |

---

## Packages used

| Package | Role |
|---|---|
| `laravel/ai` | Text-generation via any supported provider. |
| `prism-php/prism` | Underlying provider gateway used by laravel/ai. |
| `moneo/laravel-rag` | RAG/CLI/MCP backend (no Filament UI; backend-only usage). |

---

## How to run it

### 1. Configure an AI provider key

Open `.env` (or `.env.dev`) and add at least one provider key:

```bash
OPENAI_API_KEY=sk-...
# — or —
ANTHROPIC_API_KEY=sk-ant-...
# — or — (local/free model via Ollama)
OLLAMA_URL=http://localhost:11434
```

Set the default provider in `config/ai.php` (already defaults to `openai`).

### 2. Run the artisan command

```bash
# Summarise inline text
php artisan ai:summarize "Paste your long text here."

# Override provider / model
php artisan ai:summarize "Some long text." --provider=anthropic --model=claude-3-5-haiku-20241022

# Pipe text via STDIN
cat report.txt | php artisan ai:summarize --stdin

# Use a local Ollama model (no API key needed)
php artisan ai:summarize "Text…" --provider=ollama --model=llama3
```

### 3. Use the service from code

Inject `App\Services\AI\AiTextService` via the container or directly in a controller:

```php
use App\Services\AI\AiTextService;

class MyController extends Controller
{
    public function summarize(Request $request, AiTextService $ai): string
    {
        return $ai->summarize($request->input('text'));
    }
}
```

---

## How Filament v4 incompatibility is avoided

- `moneo/laravel-rag` ships a `RagPlugin` class for Filament sidebar integration.
  That plugin is **NOT registered** in `AdminPanelProvider`.
- All RAG features used here are backend-only: `rag:index`, `rag:test`, `rag:estimate`, `rag:mcp-serve`.
- Any future Filament AI pages should be created as app-owned classes
  under `app/Filament/Pages/` using standard Filament v4 APIs,
  calling `AiTextService` as the backend, never the package's resource classes directly.

---

## RAG command reference

| Command | Purpose |
|---|---|
| `php artisan rag:estimate --model="App\\Models\\YourModel"` | Estimate embedding cost & storage |
| `php artisan rag:index "App\\Models\\YourModel"` | Build / refresh the vector index for a model |
| `php artisan rag:test "Your question" --model="App\\Models\\YourModel"` | Test retrieval & generation |
| `php artisan rag:mcp-serve --host=127.0.0.1 --port=3000` | Start the MCP server |

---

## Recommended next features

1. **Quote draft generation** — create a `QuoteDraftAgent` using the same pattern, wired to an existing `Quote`/`Invoice` model.
2. **Support-message classifier** — a `ClassifyMessageAgent` that returns a structured JSON category; use `laravel/ai` structured-output contracts.
3. **Filament v4 AI page** — a custom `app/Filament/Pages/AiSummarizePage.php` built with native Filament v4 APIs (no package resources), calling `AiTextService::summarize()`.
4. **RAG-aware question answering** — use `rag:index` to embed your records, then call the `Rag` facade from `AiTextService` for grounded answers.
5. **Background queue jobs** — wrap `SummarizeAgent::make()->queue(...)` in a Laravel job for async processing.
