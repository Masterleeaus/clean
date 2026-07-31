# Build MCP Apps Guide

**Tool**: Build MCP Apps - ChatGPT Plugin  
**Purpose**: Scaffold MCP servers, generate endpoints, create backend services with full stack  
**Best For**: Backend Code Agents, Planning Agents, Execution Agents

---

## When to Use

### Designing Backend Services
- Planning new API endpoints
- Designing data models and schemas
- Planning MCP tool implementations
- Architecting service boundaries

### Building Backend Services
- Scaffolding MCP server projects
- Generating OpenAPI specifications
- Creating endpoint implementations
- Generating authentication flows

### Integration & Deployment
- Creating ChatGPT plugin UIs (Skybridge)
- Generating deployment configurations
- Creating plugin manifests
- Setting up service communication

---

## How to Use

### Scaffold MCP Server
```
"Use Build MCP Apps to scaffold an MCP server with:
- /customers GET (list all) and POST (create new)
- /customers/{id} GET (detail) and PATCH (update)
- /jobs GET (list) and POST (create)
- /invoices GET (list) and POST (create)
- Include JWT authentication"

"Use Build MCP Apps to create a WorkCore MCP with:
- Database schema (customers, jobs, invoices)
- REST endpoints for CRUD operations
- Error handling and validation
- Rate limiting configuration"
```

### Generate Specific Endpoints
```
"Use Build MCP Apps to generate [resource] endpoints following REST patterns"
"Use Build MCP Apps to create authentication endpoint with JWT tokens"
"Use Build MCP Apps to design webhook handlers for [system]"
```

### Create Plugin Integration
```
"Use Build MCP Apps to generate ChatGPT plugin UI (Skybridge) for this API"
"Use Build MCP Apps to create plugin manifest for marketplace"
"Use Build MCP Apps to design tool definitions for MCP integration"
```

### Plan API Architecture
```
"Use Build MCP Apps to help design API for [feature] with:
- Endpoint structure
- Request/response schemas
- Error handling approach
- Authentication mechanism"
```

---

## Integration with Agent Workflow

### Backend Code Agent (Pass 2)
- **Goal**: Backend Implementation
- **Use Build MCP Apps to**: Generate service skeleton, create endpoints
- **Output**: Working MCP service with basic endpoints

### Backend Code Agent (Pass 3)
- **Goal**: Hardening & Integration
- **Use Build MCP Apps to**: Add auth flows, error handling, validations
- **Output**: Production-ready backend service

### Planning Agent (Pass 2)
- **Goal**: API Design
- **Use Build MCP Apps to**: Help design API surface, endpoint structure
- **Output**: OpenAPI spec, endpoint documentation

### Execution Agent (Pass 1-2)
- **Goal**: Setup & Foundation
- **Use Build MCP Apps to**: Initialize service scaffolding
- **Output**: Ready-to-customize backend project

---

## What Build MCP Apps Generates

| Type | Examples |
|------|----------|
| **Service Structure** | MCP server boilerplate, project layout |
| **Endpoints** | REST CRUD, RPC methods, streaming handlers |
| **Data Layer** | Schema definitions, migrations, ORM setup |
| **Auth** | JWT implementation, OAuth2 flows, API keys |
| **Error Handling** | Custom exceptions, error responses, logging |
| **Validation** | Request validation, schema validation |
| **Testing** | Endpoint tests, integration test stubs |
| **Documentation** | OpenAPI spec, endpoint descriptions |
| **Deployment** | Docker setup, CI/CD templates, config |
| **Plugin** | Skybridge UI, plugin manifest, tool definitions |

---

## Service Architecture

**Generated Stack:**
- **Runtime**: Node.js, Python, or specified language
- **Framework**: Express, FastAPI, or framework-specific MCP
- **Database**: PostgreSQL/MySQL ready, SQLite for dev
- **Auth**: JWT with refresh tokens, or OAuth2
- **API Style**: RESTful or RPC depending on choice
- **Deployment**: Containerized (Docker ready)

---

## Capabilities & Limitations

**Strengths:**
- Full-stack service scaffolding
- OpenAPI spec generation
- Authentication boilerplate
- Plugin manifest generation
- Endpoint implementation stubs
- Database migration setup

**Limitations:**
- Needs integration into Titan architecture
- Complex business logic requires manual implementation
- Database queries need customization for your data model
- Some enterprise features need configuration
- Performance tuning needed for scale

---

## Workflow Integration

### Backend Agent Example (Code Agent)
```
Pass 1: Planning & Architecture
  → Use Superpowers to design API
  → Use Build MCP Apps to validate design, create OpenAPI

Pass 2: Implementation
  → Use Build MCP Apps to scaffold service
  → Implement business logic in generated endpoints
  → Add custom validators and error handling

Pass 3: Hardening
  → Use CodeRabbit to review implementation
  → Add comprehensive error handling
  → Create integration tests

Pass 4: Documentation & Deploy
  → Update endpoint docs in OpenAPI
  → Use Manufact to setup deployment
  → Document in .titan how to extend service
```

---

## Examples in Practice

### Example 1: WorkCore API Service
```
Task: "Create WorkCore MCP service"
Query: "Use Build MCP Apps to scaffold a WorkCore service with:
- /customers endpoints (GET, POST, PATCH)
- /jobs endpoints (GET, POST, PATCH, DELETE)
- /invoices endpoints (GET, POST)
- /quotes endpoints for quick pricing
- JWT authentication
- Request validation
- Error handling"
Result: Complete MCP server structure
Next: Customize business logic, add real database
```

### Example 2: ChatGPT Plugin Integration
```
Task: "Create plugin for existing API"
Query: "Use Build MCP Apps to generate ChatGPT UI for this existing API:
- [OpenAPI spec URL]
- Functions: list customers, create job, get invoice"
Result: Plugin manifest, Skybridge UI, tool definitions
Next: Deploy plugin, test in ChatGPT
```

### Example 3: Microservice Scaffold
```
Task: "Setup new payment service"
Query: "Use Build MCP Apps to scaffold a payment service MCP with:
- /payments endpoints (POST for creation, GET for status)
- /webhooks for processing (POST)
- Idempotency key support
- Extensive error handling
- Stripe/Payment gateway integration points"
Result: Service skeleton with payment patterns
Next: Integrate with payment provider, add real DB
```

---

## Tips for Effective Use

1. **Be Comprehensive**: List all endpoints needed upfront
2. **Specify Auth**: Clearly state authentication requirements
3. **Include Validation**: Describe validation rules for inputs
4. **Plan Error Cases**: Think about failure scenarios
5. **Design for Integration**: Consider how service will interact with others

---

## Common Implementation Tasks

1. **Business Logic**: Replace stub logic with real implementations
2. **Database**: Configure connection, write queries, setup migrations
3. **Authentication**: Integrate with your auth provider
4. **Validation**: Add domain-specific validation rules
5. **Error Handling**: Customize error responses and logging
6. **Testing**: Create comprehensive test suites
7. **Monitoring**: Add health checks and metrics
8. **Documentation**: Update OpenAPI with real details

---

## Related Tools

- **Superpowers**: Use to design API before building with Build MCP Apps
- **CodeRabbit**: Review generated code for quality and security
- **GitHub**: Use to understand existing services before designing new ones
- **Manufact**: Deploy generated service to production
- **Process Documentation AI**: Create API documentation and runbooks

---

## Integration into Titan Architecture

Generated services are designed to work within Titan's agent ecosystem:
- MCP protocol compliance
- Tool definition compatibility
- Plugin marketplace ready
- Cross-agent communication support

See `.titan/blueprints/04-AI-CORE-BLUEPRINT.md` for integration patterns.

---

**Status**: Ready to use  
**Last Updated**: July 31, 2026
