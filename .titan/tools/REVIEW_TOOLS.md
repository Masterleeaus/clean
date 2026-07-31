# CodeRabbit Review Tools Guide

**Tool**: CodeRabbit (ChatGPT Plugin)  
**Purpose**: Automated code review, bug detection, security analysis, quality enforcement  
**Best For**: Code Agents (all passes), Research Agents (quality audits)

---

## When to Use

### After Implementation (Pass 2-3)
- Your code is written but needs quality review
- You want to catch bugs before testing
- You need security and best practice validation
- Looking for potential edge cases

### Hardening Phase (Pass 3)
- Defensive programming checks
- Test coverage analysis
- Performance optimization suggestions
- Error handling completeness

### Before PR (Pass 4)
- Final quality gate
- Compliance with patterns
- Complete code review before pushing

---

## How to Use

### Review Code Changes
```
"Use CodeRabbit to review my changes for bugs and security issues"
"Use CodeRabbit to analyze this PR for quality problems"
"Use CodeRabbit to check my implementation against best practices"
```

### Bug Detection
```
"Use CodeRabbit to find potential bugs in my fix"
"Use CodeRabbit to check for null pointer issues in this code"
"Use CodeRabbit to identify race conditions in my changes"
```

### Security Analysis
```
"Use CodeRabbit to review for security vulnerabilities"
"Use CodeRabbit to check for injection vulnerabilities"
"Use CodeRabbit to verify authentication/authorization in my code"
```

### Test Coverage
```
"Use CodeRabbit to suggest missing test cases"
"Use CodeRabbit to verify my tests cover edge cases"
"Use CodeRabbit to find uncovered code paths"
```

### Best Practices
```
"Use CodeRabbit to check style and conventions"
"Use CodeRabbit to identify performance improvements"
"Use CodeRabbit to suggest refactoring opportunities"
```

---

## Integration with Agent Workflow

### Code Agent (Pass 2)
- **Goal**: Fix Implementation
- **Use CodeRabbit after**: Writing your fix
- **Check for**: Obvious bugs, security issues, test gaps
- **Action**: Refine implementation based on feedback

### Code Agent (Pass 3)
- **Goal**: Hardening & Tests
- **Use CodeRabbit to**: Verify defensive checks, confirm edge cases
- **Output**: Quality-assured code ready for PR

### Research Agent (Pass 2-3)
- **Goal**: Code Quality Audit
- **Use CodeRabbit to**: Scan codebase for quality issues
- **Output**: Quality findings, improvement recommendations

---

## What CodeRabbit Checks

| Category | Examples |
|----------|----------|
| **Bugs** | Null pointers, undefined behavior, logic errors |
| **Security** | Injection, XSS, auth bypass, data exposure |
| **Performance** | N+1 queries, memory leaks, inefficient algorithms |
| **Style** | Naming, consistency, readability |
| **Testing** | Coverage gaps, missing edge cases |
| **Best Practices** | Error handling, resource cleanup, patterns |

---

## Rate Limits & Account

- **Limit**: May vary by account tier
- **Free Tier**: Limited reviews per month
- **Paid Tier**: Unlimited reviews
- **Setup**: Requires CodeRabbit account connection

---

## Limitations

- Cannot fix code automatically (suggests only)
- May miss architecture-level issues
- Needs actual code/diff (not just descriptions)
- Effectiveness depends on code clarity

---

## Examples in Practice

### Example 1: Bug Fix Code Review (Code Agent, Pass 3)
```
Pass 2: You implemented the auth fix
Query: "Use CodeRabbit to review my authentication fix"
Result: "Found: missing null check on token, suggests error handling"
Action: Add defensive checks, re-run CodeRabbit
```

### Example 2: Security Audit (Research Agent, Pass 2)
```
Task: "Audit API security"
Query: "Use CodeRabbit to analyze the API endpoints for security issues"
Result: "Found 3 injection vulnerabilities, 1 auth bypass"
Output: Security findings for recommendations
```

### Example 3: Quality Baseline (Research Agent, Pass 3)
```
Task: "Assess code quality"
Query: "Use CodeRabbit to scan the entire codebase for common issues"
Result: List of quality problems, patterns
Output: Quality metrics and improvement recommendations
```

---

## Tips for Effective Use

1. **Be Ready for Changes**: Review suggestions carefully, don't blindly apply
2. **Understand Why**: Ask CodeRabbit to explain suggestions
3. **Context Matters**: Provide surrounding code context for better analysis
4. **Multiple Passes**: Run again after fixes to verify improvement
5. **Team Alignment**: Use findings to update team coding standards

---

## Integration Points

- **With GitHub**: Find code → Use GitHub to understand, use CodeRabbit to review
- **With Superpowers**: Use Superpowers to design, use CodeRabbit to verify implementation
- **With Build MCP Apps**: Generate with Build MCP Apps, refine with CodeRabbit
- **Workflow**: Implement → CodeRabbit Review → Refine → Commit

---

## Common Fixes After CodeRabbit

1. **Null checks**: Add defensive nil/undefined checks
2. **Error handling**: Wrap risky operations in try/catch
3. **Input validation**: Sanitize all external input
4. **Test cases**: Add missing edge case tests
5. **Performance**: Fix identified bottlenecks

---

**Status**: Ready to use  
**Last Updated**: July 31, 2026
