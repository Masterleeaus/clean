# Intent Layer

The Intent layer converts a human goal into a structured statement of intent before planning begins.

It owns goals, classifications, constraints, ambiguity records and intent-resolution contracts. It does not execute workflows or modify code.

Canonical flow:

```text
Human goal
→ intent
→ constraints
→ ambiguity resolution
→ planner
→ execution plan
→ validation
→ learning
```

The v1.0 bootstrap defines this boundary only. Automated intent classification and resolution remain planned.
