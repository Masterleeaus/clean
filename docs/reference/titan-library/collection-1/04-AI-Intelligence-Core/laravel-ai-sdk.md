# Laravel AI SDK setup

## What was installed
- Added `laravel/ai` to Composer dependencies.
- Installed version: `v0.6.1`.

## What was published
Executed:
- `php artisan vendor:publish --provider="Laravel\\Ai\\AiServiceProvider"`

Published assets:
- `config/ai.php`
- `database/migrations/2026_01_11_000001_create_agent_conversations_table.php`
- `stubs/agent.stub`
- `stubs/structured-agent.stub`
- `stubs/tool.stub`
- `stubs/agent-middleware.stub`

## Migrations added/run
- Added migration file:
  - `database/migrations/2026_01_11_000001_create_agent_conversations_table.php`
- The migration creates:
  - `agent_conversations`
  - `agent_conversation_messages`

### Runtime note for this environment
The default MySQL connection in this sandbox is unavailable (`SQLSTATE[HY000] [2002] Connection refused`), so full app DB migration could not be executed against MySQL here.

To validate migration safety, the Laravel AI migration was executed successfully against a local SQLite database using:
- `DB_CONNECTION=sqlite DB_DATABASE=<project>/database/ai_sdk.sqlite php artisan migrate --force --path=database/migrations/2026_01_11_000001_create_agent_conversations_table.php`

## How to create an agent
Use the published stub via Artisan:
- `php artisan make:agent SupportAgent`

Basic example pattern:
```php
use Laravel\Ai\Agent;

class SupportAgent extends Agent
{
    protected string $name = 'support-agent';

    protected string $instructions = 'Help users with concise, accurate support answers.';
}
```

## How to add tools
Use the published tool stub:
- `php artisan make:tool SearchKnowledgeBaseTool`

Then register/use the tool in your agent according to Laravel AI tool APIs.

## How to use structured output
Use the structured agent stub:
- `php artisan make:structured-agent ExtractOrderDataAgent`

Define a schema/DTO and return typed structured data from the agent instead of free-form text.

## Risks / deferred follow-up
- Run `php artisan migrate --force` in the target environment where the production DB is reachable.
- Configure provider keys in environment variables used by `config/ai.php` (for example `OPENAI_API_KEY`, `ANTHROPIC_API_KEY`, etc.).
- Decide which AI provider should be primary for this app and adjust `config/ai.php` defaults accordingly.
