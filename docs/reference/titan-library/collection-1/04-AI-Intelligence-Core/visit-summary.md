# AI Visit / Job Summary Generation

Generates structured, professional visit and job summaries from raw field notes using the configured AI provider.

This is the **fourth app-level AI feature** in this codebase. It follows the same agent → value-object → service method → artisan command pattern established by the first three features.

---

## Overview

Cleaners, technicians, and field staff often leave raw, informal notes after a visit. This feature turns those notes into:

- A concise **internal summary** for operational records and CRM history
- An optional **customer-facing summary** safe for service-completion emails and reports
- A structured list of **issues / observations** from the visit
- A list of **follow-up actions** required
- Suggested **tags** for routing, reporting, or CRM categorisation

---

## Files

| File | Role |
|---|---|
| `app/Services/AI/VisitSummaryInput.php` | Readonly DTO — structured visit inputs |
| `app/Services/AI/VisitSummaryResult.php` | Readonly DTO — structured AI output with `fromRawText()` parser |
| `app/Services/AI/Agents/VisitSummaryAgent.php` | `laravel/ai` Agent — system prompt and `buildPrompt()` factory |
| `app/Services/AI/AiTextService.php` | Extended with `summarizeVisit(VisitSummaryInput): VisitSummaryResult` |
| `app/Console/Commands/AiSummarizeVisitCommand.php` | Artisan `ai:summarize-visit` entry point |

---

## Artisan Command

```bash
php artisan ai:summarize-visit --help
```

### Basic usage

```bash
php artisan ai:summarize-visit \
  --notes="Cleaned all bathrooms and kitchen. Found mould behind the shower recess. Restocked supplies."
```

### Full context

```bash
php artisan ai:summarize-visit \
  --notes="Completed full interior clean. Kitchen oven heavily soiled — required extra degrease cycle. Mould spotted behind shower. Carpets vacuumed and spot-treated." \
  --service="Residential deep clean" \
  --property="3-bedroom house" \
  --work="Full interior clean including oven" \
  --issues="Mould behind shower recess" \
  --products="Heavy-duty degreaser, mould spray" \
  --time="3.5 hours" \
  --follow-up="Quote for mould remediation" \
  --customer-facing
```

### Read notes from STDIN

```bash
cat /tmp/job-notes.txt | php artisan ai:summarize-visit --stdin --service="Office clean" --customer-facing
```

### Override provider / model

```bash
php artisan ai:summarize-visit \
  --notes="..." \
  --provider=ollama \
  --model=llama3
```

### Debug raw AI output

```bash
php artisan ai:summarize-visit --notes="..." --raw
```

---

## Example Output

```
Generating visit summary via provider: openai…

INTERNAL SUMMARY
Full residential deep clean completed across all rooms. Oven required an additional degreasing cycle due to heavy carbon build-up. Mould growth observed behind shower recess; remediation recommended.

CUSTOMER SUMMARY
Your property has been thoroughly cleaned, including a full oven degrease and carpet spot treatment. Our team has noted a couple of follow-up items which we will be in touch about shortly.

ISSUES / OBSERVATIONS
  - Heavy soiling in oven — extra degreasing cycle applied
  - Mould growth behind shower recess — remediation recommended

FOLLOW-UP ACTIONS
  - Provide quote for mould remediation
  - Confirm customer has received service report

TAGS  deep_clean, mould, follow_up_required, oven, residential
```

---

## Input Fields (VisitSummaryInput)

| Field | Type | Description |
|---|---|---|
| `rawNotes` | `string` | Raw field notes or technician memo — **required** |
| `serviceType` | `string` | Type of service (e.g. "Residential deep clean") |
| `propertyType` | `string` | Property type (e.g. "3-bedroom house", "Office 400 m²") |
| `workCompleted` | `string` | High-level work completed description |
| `issuesFound` | `string` | Issues or defects observed during the visit |
| `productsUsed` | `string` | Products or materials consumed / applied |
| `timeOnSite` | `string` | Time spent on site (e.g. "2.5 hours") |
| `followUpNeeded` | `string` | Follow-up tasks required after the visit |
| `customerFacing` | `bool` | Generate a customer-facing summary (default: `false`) |
| `metadata` | `array` | Optional key→value extras (e.g. `['job_number' => 'JOB-1042']`) |

---

## Output Fields (VisitSummaryResult)

| Field | Type | Description |
|---|---|---|
| `internalSummary` | `string` | Operational summary for internal records |
| `customerSummary` | `string` | Polished customer-facing paragraph (empty if not requested) |
| `issues` | `list<string>` | Bullet-parsed list of issues / observations |
| `followUpActions` | `list<string>` | Bullet-parsed list of required follow-up actions |
| `tags` | `list<string>` | Comma-parsed list of routing/reporting tags |
| `rawResponse` | `string` | Full raw AI response (for debugging) |
| `isUsable()` | `bool` | Returns `true` when `internalSummary` is non-empty |

---

## Programmatic Usage

```php
use App\Services\AI\AiTextService;
use App\Services\AI\VisitSummaryInput;

$ai = app(AiTextService::class);

$input = new VisitSummaryInput(
    rawNotes:       'Completed all rooms. Mould noted behind shower.',
    serviceType:    'Residential deep clean',
    propertyType:   '3-bedroom house',
    issuesFound:    'Mould behind shower recess',
    followUpNeeded: 'Quote for mould remediation',
    customerFacing: true,
);

$result = $ai->summarizeVisit($input);

if ($result->isUsable()) {
    // Store internally
    $job->update(['summary' => $result->internalSummary]);

    // Email to customer
    if ($result->customerSummary) {
        Mail::to($customer)->send(new ServiceCompletionMail($result->customerSummary));
    }

    // Tag for CRM routing
    $job->attachTags($result->tags);
}
```

---

## Wiring to a Job / Task / Attendance Model

This feature is designed to be called from:

- A **Job / Task model observer** (`saved` / `updated`) when notes are updated
- A **Filament Action** on a job record page ("Generate Summary" button)
- A **queued job** after a technician submits field notes via mobile
- A **webhook handler** that receives completed visit data from a scheduling tool

Because all logic lives in `AiTextService::summarizeVisit()`, wiring is a single method call with no coupling to the command layer.

---

## Graceful Failure

When no AI provider key is configured, `AiTextService::summarizeVisit()` returns an empty `VisitSummaryResult` and logs a warning — it never throws. The artisan command exits with a non-zero status and prints a helpful message.

---

## Remaining Risks

| Risk | Mitigation |
|---|---|
| AI provider cost | Summarisation is on-demand only; not triggered automatically |
| Hallucination | `rawResponse` is preserved for human review; prompt instructs the model not to invent facts |
| Sensitive field data in prompts | Avoid passing PII (client names, addresses) in `metadata`; use job reference numbers instead |
| Provider latency | Wrap in a queued job for production workflows where responsiveness matters |
