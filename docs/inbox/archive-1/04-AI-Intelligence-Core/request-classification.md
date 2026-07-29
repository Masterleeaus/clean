# Third AI Feature — Request / Enquiry Classification

## What was built

A reusable AI classification layer that takes any inbound message or enquiry
and routes it into practical service-business categories with priority, department,
and recommended next-action output.

No vendor files were modified and no Filament UI components were added.

### New files

| File | Purpose |
|---|---|
| `app/Services/AI/ClassificationInput.php` | Readonly value-object for all classification inputs (message body, subject, channel, sender type, urgency hints, metadata). |
| `app/Services/AI/ClassificationResult.php` | Readonly value-object for the structured AI output (category, priority, department, recommended action, confidence, tags). Parses the agent's labelled-line response. |
| `app/Services/AI/Agents/RequestClassifierAgent.php` | laravel/ai Agent with a service-business routing system prompt and `buildPrompt()` factory. |
| `app/Console/Commands/AiClassifyRequestCommand.php` | Artisan command entry-point (`ai:classify-request`). |
| `docs/ai/request-classification.md` | This document. |

### Modified files

| File | Change |
|---|---|
| `app/Services/AI/AiTextService.php` | Added `classifyRequest(ClassificationInput, ?provider, ?model): ClassificationResult` method. |

---

## Packages used

| Package | Role |
|---|---|
| `laravel/ai` | Text-generation via any configured provider. |
| `prism-php/prism` | Underlying provider gateway used by laravel/ai. |

laravel-rag is **not used** here.

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
# Minimal — just a message body
php artisan ai:classify-request \
  --message="Hi, I need to move my Thursday clean to Friday if possible."

# Full context — better accuracy
php artisan ai:classify-request \
  --message="Hi, I need to move my Thursday clean to Friday if possible." \
  --subject="Reschedule request" \
  --channel=email \
  --sender=existing_customer \
  --existing

# Complaint example
php artisan ai:classify-request \
  --message="The cleaner left without finishing the kitchen and didn't clean the bathrooms." \
  --channel=sms \
  --sender=existing_customer \
  --existing \
  --urgency="Customer sounded angry"

# Billing query
php artisan ai:classify-request \
  --message="I was charged twice this month, please check my account." \
  --channel=email \
  --sender=existing_customer

# Override provider/model
php artisan ai:classify-request \
  --message="Do you service the Bondi area?" \
  --provider=anthropic --model=claude-3-5-haiku-20241022

# Local Ollama (no API key needed)
php artisan ai:classify-request \
  --message="Can I get a quote for a fortnightly house clean?" \
  --provider=ollama --model=llama3

# Read message from STDIN (useful for piping)
echo "I need to cancel my booking next week." | php artisan ai:classify-request --stdin

# Show raw AI response alongside parsed output (useful for debugging)
php artisan ai:classify-request --message="..." --raw
```

### 3. Example output

```
Category:              reschedule_request
Priority:              normal
Department:            bookings
Recommended action:    Check availability for Friday and reply with two time options.
Confidence:            High — explicit reschedule request with day mentioned.
Tags:                  reschedule, existing_customer, email
```

### 4. Use the service from code

```php
use App\Services\AI\AiTextService;
use App\Services\AI\ClassificationInput;

$input = new ClassificationInput(
    messageBody:         'Hi, I need to move my Thursday clean to Friday.',
    subject:             'Reschedule request',
    sourceChannel:       'email',
    senderType:          'existing_customer',
    isExistingCustomer:  true,
);

$result = app(AiTextService::class)->classifyRequest($input);

if ($result->isUsable()) {
    echo $result->category;           // e.g. "reschedule_request"
    echo $result->priority;           // e.g. "normal"
    echo $result->department;         // e.g. "bookings"
    echo $result->recommendedAction;  // plain-language next step
    echo $result->confidenceNote;     // why the model is confident
    $result->tags;                    // list<string>
}
```

### 5. Wiring to the Ticket / Lead model (future)

When the `Ticket` (support issue) or `Lead` model receives a new inbound message,
inject `AiTextService` and call `classifyRequest()`. Use the returned `category`
to pre-fill the ticket type and `department` to suggest an agent group.

Example (inside a listener or job):

```php
$result = $ai->classifyRequest(new ClassificationInput(
    messageBody: $ticket->description,
    sourceChannel: $ticket->channel?->name ?? '',
    senderType: 'existing_customer',
    isExistingCustomer: true,
));

if ($result->isUsable()) {
    $ticket->update([
        'ai_category'           => $result->category,
        'ai_priority'           => $result->priority,
        'ai_recommended_action' => $result->recommendedAction,
    ]);
}
```

---

## Category reference

| Category slug | Description |
|---|---|
| `quote_request` | Customer asking for a price or estimate. |
| `booking_request` | New service booking request. |
| `reschedule_request` | Existing booking change request. |
| `complaint_service_issue` | Dissatisfaction or quality complaint. |
| `billing_payment_issue` | Invoice dispute, overcharge, payment question. |
| `general_enquiry` | Non-specific question about services or areas. |
| `team_internal` | Internal message from staff. |
| `spam_irrelevant` | Spam, bot, or clearly off-topic content. |

---

## Priority reference

| Value | Meaning |
|---|---|
| `urgent` | Safety, legal, or same-day operational risk. |
| `high` | Same-day or next-day response required. |
| `normal` | 24–48 hour response acceptable (default). |
| `low` | Non-time-sensitive, informational. |

---

## Input fields reference

| Field | CLI option | Description |
|---|---|---|
| Message body | `--message` | **Required** (or `--stdin`). The raw inbound text. |
| Subject | `--subject` | Email subject or ticket title. |
| Source channel | `--channel` | `email`, `sms`, `web_form`, `phone_note`, `live_chat`, `internal`. |
| Sender type | `--sender` | `existing_customer`, `prospect`, `team_member`, `unknown`. |
| Existing customer | `--existing` | Flag (boolean) — improves priority/action decisions. |
| Urgency hints | `--urgency` | Any urgency signals already detected (free text). |

---

## Recommended next features

1. **Auto-classify on ticket creation** — attach a `classified` observer to the `Ticket` model and run `classifyRequest()` asynchronously via a queued job.
2. **Filament v4 Action** — a "Classify" action on the Ticket/Lead resource list that calls `AiTextService::classifyRequest()` and displays results in a modal.
3. **Lead scoring integration** — use `category` + `priority` output to automatically update `Lead::$next_follow_up` and assign an agent.
4. **Confidence threshold routing** — only auto-assign when `confidence` contains "high"; queue for human review otherwise.
