# GitHub Plugin Guide

**Tool**: GitHub Plugin (ChatGPT)  
**Purpose**: Navigate repositories, search code, understand architecture, review commit history  
**Best For**: Code Agents, Research Agents (all agent types)

---

## When to Use

### Search for Code
- Finding where a function/class is defined
- Locating error messages or specific patterns
- Discovering related files by keyword
- Browsing repo structure and organization

### Understand Existing Code
- Reading implementation details
- Tracing function calls and dependencies
- Understanding business logic
- Learning from existing patterns

### Review History
- Browsing commit history for a file
- Finding what changed and why
- Understanding past decisions
- Checking blame for specific lines

### Query Issues & PRs
- Finding related issues
- Searching PR discussions
- Looking at reviews and feedback
- Understanding what's planned

---

## How to Use

### Search Code
```
"Use GitHub to search for [function/class name] in the codebase"
"Use GitHub to find all files containing [pattern]"
"Use GitHub to locate where [error message] originates"
```

### Read Implementation
```
"Use GitHub to show me the implementation of [function]"
"Use GitHub to read the [file path] and explain what it does"
"Use GitHub to browse the file structure of [directory]"
```

### Trace Dependencies
```
"Use GitHub to find all files that import/use [function]"
"Use GitHub to show me the call chain for [API endpoint]"
"Use GitHub to find references to [class name]"
```

### Review Changes
```
"Use GitHub to show me the commit history for [file]"
"Use GitHub to explain why [file] was changed in commit [hash]"
"Use GitHub to show all commits by [author] affecting [directory]"
```

---

## Integration with Agent Workflow

### Code Agent (Pass 1)
- **Goal**: Investigation & Root Cause
- **Use GitHub to**: Find error location, understand affected files, trace root cause
- **Output**: List of relevant files, understanding of flow

### Research Agent (Pass 1-2)
- **Goal**: Initial Investigation → Deep Analysis
- **Use GitHub to**: Scan codebase for audit targets, find patterns, examine security boundaries
- **Output**: Audit scope, identified issues, documented findings

### Planning Agent (Pass 1)
- **Goal**: Requirements & Scope
- **Use GitHub to**: Understand current implementation, identify extension points
- **Output**: Design requirements, affected components

---

## Rate Limits
- **Limit**: Unlimited for repositories you have access to
- **Note**: No rate limiting on read-only searches
- **Account**: Works with GitHub token from ChatGPT auth

---

## Limitations
- Read-only: Cannot push commits or create PRs
- Search breadth: May miss code in unusual locations
- Documentation: Relies on code comments and docstrings
- History: Limited to visible branches

---

## Examples in Practice

### Example 1: Bug Fix (Code Agent, Pass 1)
```
Issue: "Authentication fails in production"
Query: "Use GitHub to search for AuthenticationError in the codebase"
Result: Found in src/middleware/auth.js line 45
Follow-up: "Use GitHub to show me how auth middleware is used"
```

### Example 2: Security Audit (Research Agent, Pass 1)
```
Task: "Audit authentication mechanisms"
Query: "Use GitHub to find all places where auth tokens are handled"
Result: Found token validation in 5 files
Follow-up: "Use GitHub to show token validation implementations"
```

### Example 3: Architecture Review (Planning Agent)
```
Task: "Plan API expansion"
Query: "Use GitHub to show me the current API endpoint structure"
Result: Endpoints organized in /api/v1/, /api/v2/ directories
Follow-up: "Use GitHub to find the pattern used for endpoint definitions"
```

---

## Tips for Effective Use

1. **Be Specific**: Use exact names or patterns, not vague descriptions
2. **Start Broad**: Search for concepts, then narrow to specific files
3. **Follow Context**: Look at related files after finding initial location
4. **Check History**: Understand why something was implemented that way
5. **Search Related**: Find similar patterns to learn codebase conventions

---

## Integration Points

- **With CodeRabbit**: Use GitHub to find code, use CodeRabbit to review it
- **With Build Web Apps**: Use GitHub to understand current UI, then use Build Web Apps to extend it
- **With Build MCP Apps**: Use GitHub to understand current APIs, then design new ones
- **With Superpowers**: Use GitHub to understand scope, use Superpowers to plan changes

---

**Status**: Ready to use  
**Last Updated**: July 31, 2026
