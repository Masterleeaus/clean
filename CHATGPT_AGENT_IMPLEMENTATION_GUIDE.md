# ChatGPT Agent Implementation Guide

**Purpose:** Step-by-step guide to implement ChatGPT agent workflows and actions  
**Target:** Repository owners, DevOps engineers, agent developers  
**Scope:** GitHub Actions workflows, Artisan commands, API endpoints

---

## Phase 1: GitHub Actions Infrastructure

### 1.1 Create Workflow Files Structure

Create these files in `.github/workflows/`:

```
.github/
├── workflows/
│   ├── chatgpt-agent-main.yml              (Master dispatcher)
│   ├── chatgpt-analyze.yml                 (Analysis workflows)
│   ├── chatgpt-validate.yml                (Validation workflows)
│   ├── chatgpt-test.yml                    (Testing workflows)
│   ├── chatgpt-export.yml                  (Export/documentation)
│   └── README.md                           (Workflow documentation)
├── scripts/
│   ├── chatgpt-agent/
│   │   ├── analyze-structure.sh
│   │   ├── validate-extensions.sh
│   │   ├── export-commands.sh
│   │   ├── export-schemas.sh
│   │   └── test-integration.sh
│   └── README.md
└── CODEOWNERS                              (Add workflow ownership)
```

### 1.2 Master Dispatcher Workflow

Create `.github/workflows/chatgpt-agent-main.yml`:

```yaml
name: ChatGPT Agent Master Dispatcher

on:
  workflow_dispatch:
    inputs:
      action:
        description: 'Action to execute'
        required: true
        type: choice
        options:
          - analyze-structure
          - validate-extensions
          - export-command-registry
          - export-schemas
          - validate-wizards
          - run-tests
          - test-capability
          - audit-domain
          - analyze-dependencies
          - generate-docs

      target:
        description: 'Target domain/extension (optional)'
        required: false

      params:
        description: 'Additional parameters (JSON)'
        required: false

  workflow_call:
    inputs:
      action:
        required: true
        type: string
      target:
        required: false
        type: string

concurrency:
  group: chatgpt-${{ github.event.inputs.action || inputs.action }}
  cancel-in-progress: false

jobs:
  dispatch:
    name: Execute ${{ github.event.inputs.action || inputs.action }}
    runs-on: ubuntu-latest
    timeout-minutes: 30
    permissions:
      contents: read
      checks: write
      pull-requests: write

    steps:
      - name: Checkout
        uses: actions/checkout@v4
        with:
          fetch-depth: 0

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: dom,curl,libxml,mbstring,zip,pcntl,pdo,sqlite,pdo_sqlite
          ini-values: memory_limit=2G
          tools: composer:v2

      - name: Setup Node
        uses: actions/setup-node@v3
        with:
          node-version: '18'
          cache: 'npm'

      - name: Cache PHP dependencies
        uses: actions/cache@v3
        with:
          path: vendor
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install PHP dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Install Node dependencies
        run: npm ci

      - name: Analyze Repository Structure
        if: ${{ github.event.inputs.action == 'analyze-structure' || inputs.action == 'analyze-structure' }}
        run: php artisan chatgpt:analyze-structure
        continue-on-error: true

      - name: Validate Extensions
        if: ${{ github.event.inputs.action == 'validate-extensions' || inputs.action == 'validate-extensions' }}
        run: |
          php artisan extension:health-check
          php artisan extension:validate-dependencies
          php artisan extension:detect-conflicts
        continue-on-error: true

      - name: Export Command Registry
        if: ${{ github.event.inputs.action == 'export-command-registry' || inputs.action == 'export-command-registry' }}
        run: |
          php artisan workcore:export-commands --format=json > workcore-commands.json
          php artisan workcore:export-queries --format=json > workcore-queries.json
        continue-on-error: true

      - name: Export Schemas
        if: ${{ github.event.inputs.action == 'export-schemas' || inputs.action == 'export-schemas' }}
        run: |
          php artisan openapi:generate --output=docs/openapi.json
          php artisan contracts:export --format=json > domain-contracts.json
        continue-on-error: true

      - name: Validate Wizards
        if: ${{ github.event.inputs.action == 'validate-wizards' || inputs.action == 'validate-wizards' }}
        run: |
          php artisan wizard:validate-definitions --strict
          php artisan wizard:validate-commands
        continue-on-error: true

      - name: Run Tests
        if: ${{ github.event.inputs.action == 'run-tests' || inputs.action == 'run-tests' }}
        run: php artisan test --parallel
        continue-on-error: true

      - name: Test Capability
        if: ${{ github.event.inputs.action == 'test-capability' || inputs.action == 'test-capability' }}
        run: |
          php artisan workcore:invoke-capability \
            --capability="${{ github.event.inputs.target }}" \
            --dry-run \
            --verbose
        continue-on-error: true

      - name: Audit Domain
        if: ${{ github.event.inputs.action == 'audit-domain' || inputs.action == 'audit-domain' }}
        run: |
          php artisan workcore:audit-domain \
            --domain="${{ github.event.inputs.target }}" \
            --output=domain-audit.json
        continue-on-error: true

      - name: Analyze Dependencies
        if: ${{ github.event.inputs.action == 'analyze-dependencies' || inputs.action == 'analyze-dependencies' }}
        run: |
          php artisan dependency:analyze \
            --target="${{ github.event.inputs.target }}" \
            --format=json > dependencies.json
        continue-on-error: true

      - name: Generate Documentation
        if: ${{ github.event.inputs.action == 'generate-docs' || inputs.action == 'generate-docs' }}
        run: |
          php artisan docs:generate \
            --output=docs/generated \
            --include-examples \
            --include-schemas
        continue-on-error: true

      - name: Upload Results
        if: always()
        uses: actions/upload-artifact@v3
        with:
          name: chatgpt-results-${{ github.run_id }}
          path: |
            workcore-commands.json
            workcore-queries.json
            domain-contracts.json
            domain-audit.json
            dependencies.json
            docs/
            *.txt
            *.log
          retention-days: 30

      - name: Create Summary
        if: always()
        run: |
          cat >> $GITHUB_STEP_SUMMARY << 'EOF'
          # ChatGPT Agent Action Results
          
          **Action:** ${{ github.event.inputs.action || inputs.action }}
          **Target:** ${{ github.event.inputs.target || 'N/A' }}
          **Status:** ${{ job.status }}
          
          Results available in artifacts: `chatgpt-results-${{ github.run_id }}`
          EOF
```

---

## Phase 2: Custom Artisan Commands

### 2.1 Create Commands Directory

```
app/Console/Commands/ChatGPT/
├── AnalyzeStructureCommand.php
├── ExportCommandRegistryCommand.php
├── ExportSchemasCommand.php
├── ValidateExtensionsCommand.php
├── ValidateWizardsCommand.php
├── TestCapabilityCommand.php
├── AuditDomainCommand.php
└── AnalyzeDependenciesCommand.php
```

### 2.2 Example Command Implementation

Create `app/Console/Commands/ChatGPT/AnalyzeStructureCommand.php`:

```php
<?php

namespace App\Console\Commands\ChatGPT;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AnalyzeStructureCommand extends Command
{
    protected $signature = 'chatgpt:analyze-structure
        {--output=structure-analysis.json : Output file}
        {--verbose : Verbose output}';

    protected $description = 'Analyze repository structure for ChatGPT agent';

    public function handle()
    {
        $analysis = [
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
            'domains' => $this->analyzeDomains(),
            'extensions' => $this->analyzeExtensions(),
            'packages' => $this->analyzePackages(),
            'routes' => $this->analyzeRoutes(),
            'migrations' => $this->analyzeMigrations(),
        ];

        $output = $this->option('output');
        File::put($output, json_encode($analysis, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Analysis saved to: {$output}");
        
        if ($this->option('verbose')) {
            $this->table(
                ['Component', 'Count'],
                [
                    ['Domains', count($analysis['domains'])],
                    ['Extensions', count($analysis['extensions'])],
                    ['Packages', count($analysis['packages'])],
                    ['API Routes', count($analysis['routes'])],
                    ['Migrations', count($analysis['migrations'])],
                ]
            );
        }
    }

    private function analyzeDomains(): array
    {
        $domains = [];
        $path = app_path('Domains');

        foreach (File::directories($path) as $domain) {
            $domainName = basename($domain);
            $domains[$domainName] = [
                'path' => "app/Domains/{$domainName}",
                'subdirectories' => count(File::directories($domain)),
                'files' => count(File::allFiles($domain)),
                'has_models' => File::exists("{$domain}/Models"),
                'has_contracts' => File::exists("{$domain}/Contracts"),
                'has_migrations' => File::exists("{$domain}/Database/Migrations"),
                'has_routes' => File::exists("{$domain}/Routes"),
            ];
        }

        return $domains;
    }

    private function analyzeExtensions(): array
    {
        $extensions = [];
        $path = app_path('Extensions');

        foreach (File::directories($path) as $extension) {
            $extensionName = basename($extension);
            $manifestPath = "{$extension}/extension.json";

            if (File::exists($manifestPath)) {
                $manifest = json_decode(File::get($manifestPath), true);
                $extensions[$extensionName] = [
                    'name' => $manifest['name'] ?? $extensionName,
                    'version' => $manifest['version'] ?? 'unknown',
                    'enabled' => $manifest['enabled'] ?? false,
                    'dependencies' => $manifest['requires'] ?? [],
                    'provides' => $manifest['provides'] ?? [],
                    'path' => "app/Extensions/{$extensionName}",
                ];
            }
        }

        return $extensions;
    }

    private function analyzePackages(): array
    {
        $packages = [];
        $path = base_path('packages');

        if (File::exists($path)) {
            foreach (File::directories($path) as $vendor) {
                foreach (File::directories($vendor) as $package) {
                    $packageName = basename($vendor) . '/' . basename($package);
                    $packages[$packageName] = [
                        'path' => 'packages/' . $packageName,
                        'has_composer' => File::exists("{$package}/composer.json"),
                        'has_src' => File::exists("{$package}/src"),
                    ];
                }
            }
        }

        return $packages;
    }

    private function analyzeRoutes(): array
    {
        $routes = [];
        $apiRoutesPath = base_path('routes/api.php');

        if (File::exists($apiRoutesPath)) {
            $content = File::get($apiRoutesPath);
            preg_match_all('/Route::(get|post|put|patch|delete)\([\'"]([^\'"]+)/i', $content, $matches);

            foreach ($matches[2] ?? [] as $route) {
                $routes[] = $route;
            }
        }

        return array_unique($routes);
    }

    private function analyzeMigrations(): array
    {
        $migrations = [];
        $path = database_path('migrations');

        if (File::exists($path)) {
            foreach (File::files($path) as $file) {
                $migrations[] = $file->getFilename();
            }
        }

        return $migrations;
    }
}
```

### 2.3 Export Commands Command

Create `app/Console/Commands/ChatGPT/ExportCommandRegistryCommand.php`:

```php
<?php

namespace App\Console\Commands\ChatGPT;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class ExportCommandRegistryCommand extends Command
{
    protected $signature = 'chatgpt:export-commands
        {--output-commands=workcore-commands.json : Commands output}
        {--output-queries=workcore-queries.json : Queries output}
        {--include-examples : Include usage examples}';

    protected $description = 'Export WorkCore command and query registry for ChatGPT';

    public function handle()
    {
        $commands = $this->discoverCommands();
        $queries = $this->discoverQueries();

        $commandsOutput = $this->option('output-commands');
        $queriesOutput = $this->option('output-queries');

        File::put($commandsOutput, json_encode($commands, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        File::put($queriesOutput, json_encode($queries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Commands exported to: {$commandsOutput}");
        $this->info("Queries exported to: {$queriesOutput}");

        $this->table(
            ['Type', 'Count'],
            [
                ['Commands', count($commands)],
                ['Queries', count($queries)],
            ]
        );
    }

    private function discoverCommands(): array
    {
        // Scan app/Domains/WorkCore/System/Commands/
        // Extract command signatures, input schemas, output schemas
        // Return structured array

        return [
            'workcore.customer.create' => [
                'category' => 'CRM',
                'description' => 'Create a new customer',
                'inputs' => [
                    'name' => ['type' => 'string', 'required' => true],
                    'email' => ['type' => 'email', 'required' => true],
                    'phone' => ['type' => 'string', 'required' => false],
                ],
                'outputs' => [
                    'customer_id' => 'uuid',
                    'created_at' => 'timestamp',
                ],
                'permissions' => ['manager', 'admin'],
                'audit' => true,
            ],
            // ... more commands
        ];
    }

    private function discoverQueries(): array
    {
        return [
            'workcore.customer.list' => [
                'category' => 'CRM',
                'description' => 'List customers for current company',
                'inputs' => [
                    'page' => ['type' => 'integer', 'default' => 1],
                    'limit' => ['type' => 'integer', 'default' => 50],
                    'search' => ['type' => 'string', 'required' => false],
                ],
                'permissions' => ['user', 'manager', 'admin'],
            ],
            // ... more queries
        ];
    }
}
```

---

## Phase 3: API Endpoints for ChatGPT

### 3.1 Create ChatGPT API Controller

Create `app/Http/Controllers/Api/ChatGPTAgentController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatGPTAgentController extends Controller
{
    /**
     * Get repository structure information
     */
    public function getStructure(): JsonResponse
    {
        $structure = [
            'domains' => $this->getDomains(),
            'extensions' => $this->getExtensions(),
            'packages' => $this->getPackages(),
            'api_routes' => $this->getApiRoutes(),
        ];

        return response()->json($structure);
    }

    /**
     * Get all available WorkCore commands
     */
    public function getCommands(Request $request): JsonResponse
    {
        $category = $request->query('category');
        $commands = app('workcore.registry')->getCommands();

        if ($category) {
            $commands = collect($commands)->filter(fn($cmd) => 
                ($cmd['category'] ?? null) === $category
            );
        }

        return response()->json([
            'total' => count($commands),
            'commands' => $commands,
        ]);
    }

    /**
     * Get command schema by name
     */
    public function getCommandSchema(string $command): JsonResponse
    {
        try {
            $schema = app('workcore.registry')->getCommandSchema($command);
            return response()->json($schema);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Command not found',
                'command' => $command,
            ], 404);
        }
    }

    /**
     * Test a capability
     */
    public function testCapability(Request $request): JsonResponse
    {
        $capability = $request->input('capability');
        $payload = $request->input('payload', []);
        
        // Validate capability exists
        $schema = app('workcore.registry')->getCommandSchema($capability);
        
        // Dry-run validation
        $validator = validator($payload, $this->getValidationRules($schema));
        
        return response()->json([
            'capability' => $capability,
            'valid' => !$validator->fails(),
            'errors' => $validator->errors(),
            'schema' => $schema,
        ]);
    }

    /**
     * Get extension information
     */
    public function getExtensions(): JsonResponse
    {
        $extensions = app('extension.registry')->all();

        return response()->json([
            'total' => count($extensions),
            'extensions' => $extensions,
        ]);
    }

    /**
     * Get wizard definitions
     */
    public function getWizards(Request $request): JsonResponse
    {
        $category = $request->query('category');
        $wizards = app('wizard.registry')->all();

        if ($category) {
            $wizards = collect($wizards)->filter(fn($w) => 
                ($w['category'] ?? null) === $category
            );
        }

        return response()->json([
            'total' => count($wizards),
            'wizards' => $wizards->values(),
        ]);
    }

    /**
     * Get wizard schema by ID
     */
    public function getWizardSchema(string $wizardId): JsonResponse
    {
        try {
            $wizard = app('wizard.registry')->get($wizardId);
            return response()->json($wizard);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Wizard not found',
                'wizard_id' => $wizardId,
            ], 404);
        }
    }

    private function getDomains(): array
    {
        // Return domain structure
        return [];
    }

    private function getExtensions(): array
    {
        // Return extension list
        return [];
    }

    private function getPackages(): array
    {
        // Return packages
        return [];
    }

    private function getApiRoutes(): array
    {
        // Return API routes
        return [];
    }

    private function getValidationRules(array $schema): array
    {
        // Convert schema to Laravel validation rules
        return [];
    }
}
```

### 3.2 Add API Routes

Add to `routes/api.php`:

```php
Route::middleware('auth:api')->prefix('v1/chatgpt-agent')->group(function () {
    Route::get('structure', [ChatGPTAgentController::class, 'getStructure']);
    Route::get('commands', [ChatGPTAgentController::class, 'getCommands']);
    Route::get('commands/{command}/schema', [ChatGPTAgentController::class, 'getCommandSchema']);
    Route::post('commands/{command}/test', [ChatGPTAgentController::class, 'testCapability']);
    Route::get('extensions', [ChatGPTAgentController::class, 'getExtensions']);
    Route::get('wizards', [ChatGPTAgentController::class, 'getWizards']);
    Route::get('wizards/{id}/schema', [ChatGPTAgentController::class, 'getWizardSchema']);
});
```

---

## Phase 4: ChatGPT Integration Points

### 4.1 Create ChatGPT Plugin Specification

Create `docs/chatgpt-plugin.yaml`:

```yaml
openapi: 3.0.0
info:
  title: Titan Zero ChatGPT Agent API
  description: API for ChatGPT agents to interact with MagicAI/WorkCore
  version: 1.0.0
  contact:
    name: API Support
    email: api@example.com

servers:
  - url: https://api.example.com

security:
  - bearerAuth: []

paths:
  /api/v1/chatgpt-agent/structure:
    get:
      tags:
        - Repository
      summary: Get repository structure
      description: Returns domain, extension, and package structure
      responses:
        '200':
          description: Repository structure

  /api/v1/chatgpt-agent/commands:
    get:
      tags:
        - WorkCore
      summary: List available commands
      parameters:
        - name: category
          in: query
          schema:
            type: string
      responses:
        '200':
          description: Command registry

  /api/v1/chatgpt-agent/commands/{command}/schema:
    get:
      tags:
        - WorkCore
      summary: Get command schema
      parameters:
        - name: command
          in: path
          required: true
          schema:
            type: string
      responses:
        '200':
          description: Command schema
        '404':
          description: Command not found

  /api/v1/chatgpt-agent/commands/{command}/test:
    post:
      tags:
        - WorkCore
      summary: Test command execution
      parameters:
        - name: command
          in: path
          required: true
          schema:
            type: string
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                payload:
                  type: object
      responses:
        '200':
          description: Test result

  /api/v1/chatgpt-agent/extensions:
    get:
      tags:
        - Extensions
      summary: List extensions
      responses:
        '200':
          description: Extension list

  /api/v1/chatgpt-agent/wizards:
    get:
      tags:
        - Wizards
      summary: List wizards
      parameters:
        - name: category
          in: query
          schema:
            type: string
      responses:
        '200':
          description: Wizard list

  /api/v1/chatgpt-agent/wizards/{id}/schema:
    get:
      tags:
        - Wizards
      summary: Get wizard schema
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: string
      responses:
        '200':
          description: Wizard schema
        '404':
          description: Wizard not found

components:
  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT
```

### 4.2 Create ChatGPT System Prompt

Create `docs/chatgpt-system-prompt.md`:

````markdown
# ChatGPT Agent System Prompt for Titan Zero Repository

You are an AI agent specialized in understanding and working with the Titan Zero MagicAI integration workspace. 

## Core Capabilities

1. **Repository Understanding**
   - Navigate complex Laravel/Vue SaaS architecture
   - Understand domain-driven design principles
   - Recognize multi-tenancy patterns and boundaries

2. **WorkCore Interactions**
   - Query available commands and capabilities
   - Validate command inputs against schemas
   - Understand permission and authorization models
   - Execute read-only queries for data understanding

3. **Extension Management**
   - Query extension capabilities
   - Understand extension dependencies
   - Identify conflicts and compatibility issues
   - Plan extension integration strategies

4. **Wizard & Workflow Design**
   - Create wizard definitions following JSON schema
   - Validate workflows against execution engine
   - Map wizard steps to WorkCore commands
   - Consider offline-first execution implications

5. **Code Analysis**
   - Understand architectural boundaries (WorkCore, Engine, Extensions)
   - Identify violations and anti-patterns
   - Suggest refactoring strategies
   - Trace code dependencies and impacts

## Key Constraints

1. **Multi-Tenancy**: All data operations must be tenant-scoped via company_id
2. **Isolation**: Extensions must never shadow WorkCore domains
3. **Security**: Never cache credentials or sensitive data in service workers
4. **Offline-First**: PWA must work without network connectivity
5. **Audit Trail**: All business actions must be auditable

## Available Information Sources

1. **API Endpoints** (requires authentication):
   - GET /api/v1/chatgpt-agent/structure
   - GET /api/v1/chatgpt-agent/commands
   - GET /api/v1/chatgpt-agent/extensions
   - GET /api/v1/chatgpt-agent/wizards

2. **GitHub Workflows**:
   - chatgpt-agent-main.yml (master dispatcher)
   - Can trigger specialized analysis workflows
   - Results available in job artifacts

3. **Repository Files**:
   - Domain structure in app/Domains/
   - Extensions in app/Extensions/
   - API routes in routes/api.php
   - Wizard definitions in packages/titan-zero/

4. **Documentation**:
   - CHATGPT_AGENT_QUICK_REFERENCE.md (quick lookup)
   - CHATGPT_AGENT_WORKFLOWS_AND_ACTIONS.md (workflow guide)
   - Domain-specific READMEs

## When to Use Workflows

- **analyze-structure**: When you need current repository state
- **validate-extensions**: Before suggesting extension changes
- **export-command-registry**: When designing workflows
- **export-schemas**: When validating data contracts
- **run-tests**: Before finalizing code changes
- **test-capability**: When verifying capability existence

## Escalation Criteria

Escalate to human review for:
- Database schema changes
- Security or encryption modifications
- Cross-domain refactoring
- Multi-step architectural changes
- Undocumented historical decisions

## Response Format

When providing guidance:
1. Explain your understanding of the architecture
2. Identify relevant code locations
3. Suggest specific implementations
4. Include validation/test strategy
5. Note any constraints or dependencies
````

---

## Phase 5: Configuration & Secrets

### 5.1 GitHub Secrets Configuration

Add to repository secrets:

```
CHATGPT_API_KEY         → For authenticated API calls
WORKFLOW_SIGNING_KEY    → For signing workflow definitions
GITHUB_TOKEN            → Already available
```

### 5.2 Environment Variables

Add to `.env.example`:

```
CHATGPT_AGENT_ENABLED=true
CHATGPT_AGENT_API_BASE=https://api.example.com
CHATGPT_RATE_LIMIT=1000
CHATGPT_CACHE_REGISTRY=true
CHATGPT_CACHE_TTL=3600
```

---

## Phase 6: Testing & Validation

### 6.1 Test Workflow Access

```bash
# Test repository access
gh workflow list

# Trigger test workflow
gh workflow run chatgpt-agent-main.yml \
  -f action=analyze-structure

# Monitor results
gh run list -w chatgpt-agent-main.yml
gh run view <run-id> --log
```

### 6.2 Test API Endpoints

```bash
# Get auth token
TOKEN=$(gh auth token)

# Test structure endpoint
curl -H "Authorization: Bearer $TOKEN" \
  https://api.example.com/api/v1/chatgpt-agent/structure

# Test commands endpoint
curl -H "Authorization: Bearer $TOKEN" \
  https://api.example.com/api/v1/chatgpt-agent/commands

# Test capability schema
curl -H "Authorization: Bearer $TOKEN" \
  https://api.example.com/api/v1/chatgpt-agent/commands/workcore.customer.create/schema
```

---

## Phase 7: Documentation & Training

### 7.1 Create Agent Training Guide

Document in `docs/chatgpt-agent-training.md`:

1. **Basic Repository Navigation**
   - How to find domain code
   - Understanding extension structure
   - Locating API definitions

2. **Working with WorkCore**
   - Common commands (customer.create, job.create, etc.)
   - Understanding permissions
   - Validation requirements

3. **Creating Workflows**
   - Wizard definition structure
   - Mapping to WorkCore commands
   - Testing strategies

4. **Common Tasks**
   - Adding new feature to extension
   - Creating new wizard
   - Integrating new capability
   - Writing tests

### 7.2 Create FAQ Document

Document common ChatGPT questions:

```markdown
# ChatGPT Agent FAQ

**Q: How do I understand what extensions are available?**
A: Run workflow `analyze-structure` or call API endpoint `/chatgpt-agent/extensions`

**Q: Can I create new WorkCore commands?**
A: No, only existing commands are available. You can map existing commands to wizards.

**Q: How do I test wizard definitions?**
A: Use workflow `validate-wizards` to validate schemas before deployment.

**Q: What permissions do I need to check?**
A: Use API `/chatgpt-agent/commands/{id}/schema` which includes required permissions.

...
```

---

## Implementation Checklist

- [ ] Create `.github/workflows/chatgpt-agent-main.yml`
- [ ] Create Artisan commands in `app/Console/Commands/ChatGPT/`
- [ ] Create API controller and routes
- [ ] Add GitHub secrets
- [ ] Test workflow dispatch and API endpoints
- [ ] Create ChatGPT plugin specification
- [ ] Document system prompt and constraints
- [ ] Create training and FAQ guides
- [ ] Set up monitoring and logging
- [ ] Create rate limiting policies
- [ ] Document escalation procedures
- [ ] Set up access controls

---

## Success Criteria

✅ ChatGPT can query repository structure  
✅ ChatGPT can discover available commands  
✅ ChatGPT can validate wizard definitions  
✅ ChatGPT can test capability schemas  
✅ ChatGPT can understand extension capabilities  
✅ ChatGPT triggers workflows successfully  
✅ ChatGPT respects security boundaries  
✅ ChatGPT escalates appropriately  

---

## Support & Maintenance

- **Monitor**: GitHub Actions usage and API rate limits
- **Update**: When new domains, commands, or extensions added
- **Review**: Monthly audit of ChatGPT workflow effectiveness
- **Escalate**: Complex architectural changes to human review
