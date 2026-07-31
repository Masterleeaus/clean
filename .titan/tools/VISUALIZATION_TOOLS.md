# Goodnotes Visualization & Diagrams Guide

**Tool**: Goodnotes - ChatGPT Plugin  
**Purpose**: Create flowcharts, architecture diagrams, mind maps, UML diagrams, visual documentation  
**Best For**: Planning Agents, Research Agents (visualizing findings), Documentation Agents

---

## When to Use

### Architecture Documentation
- Creating system architecture diagrams
- Visualizing component interactions
- Drawing deployment topology
- Showing data flow between systems

### Process Documentation
- Flowcharting workflows and processes
- Visualizing decision trees
- Drawing sequence diagrams
- Mapping process steps

### Design Communication
- Creating wireframes
- Visualizing user flows
- Drawing mental models
- Creating concept maps

### Technical Analysis
- Visualizing code structure
- Mapping dependencies
- Drawing system diagrams
- Showing audit findings

---

## How to Use

### Create Architecture Diagram
```
"Use Goodnotes to create a system architecture diagram showing:
- [Component 1] (what it does)
- [Component 2] (what it does)
- [Component 3] (what it does)
- Connections between components
- Data flow direction
- External systems

Export as SVG or image"
```

### Draw Flowchart
```
"Use Goodnotes to create a flowchart for [process]:
- Start: [entry point]
- Steps: [list steps]
- Decisions: [decision points and branches]
- End: [outcomes]

Example:
'Create flowchart for job completion workflow:
- Staff marks job complete
- System validates completion data
- If validation passes → Generate invoice
- If validation fails → Return to staff
- Send completion notification
- Archive job'"
```

### Create Sequence Diagram
```
"Use Goodnotes to create a UML sequence diagram for [interaction]:
- Actors: [who's involved]
- Sequence: [step-by-step interaction]
- Messages: [what they exchange]
- Timing: [important timing aspects]"
```

### Draw Mind Map
```
"Use Goodnotes to create a mind map of [topic]:
- Main concept: [center]
- Branches: [major categories]
- Sub-branches: [details]"
```

### Visualize Audit Findings
```
"Use Goodnotes to create a diagram showing:
- Audit scope: [what was audited]
- Findings: [visual representation of issues]
- Risk levels: [color-coded severity]
- Recommendations: [suggested fixes]"
```

---

## Integration with Agent Workflow

### Planning Agent (Pass 2)
- **Goal**: Architecture & Design
- **Use Goodnotes to**: Visualize architecture designs
- **Output**: Architecture diagrams for documentation

### Planning Agent (Pass 3)
- **Goal**: Implementation Planning
- **Use Goodnotes to**: Diagram implementation flow
- **Output**: Visual implementation roadmap

### Research Agent (Pass 3)
- **Goal**: Recommendations & Findings
- **Use Goodnotes to**: Visualize audit findings
- **Output**: Visual audit report

### Monitoring Agent (Pass 3)
- **Goal**: Analysis & Dashboards
- **Use Goodnotes to**: Design dashboard layouts
- **Output**: Dashboard mockups

---

## Diagram Types

| Type | Use Case | Examples |
|------|----------|----------|
| **Architecture** | System design | Components, interactions, deployment |
| **Flowchart** | Process design | Workflows, decision trees |
| **Sequence** | Interaction design | Actor sequences, message flows |
| **UML Class** | Data model | Classes, relationships |
| **ER Diagram** | Database | Tables, relationships |
| **Mind Map** | Concept mapping | Hierarchical relationships |
| **Timeline** | Timeline visualization | Event sequencing |
| **Swimlane** | Process with roles | Workflow by actor |

---

## Capabilities & Limitations

**Strengths:**
- Generate flowcharts from descriptions
- Create architecture diagrams automatically
- Support for UML and ER diagrams
- Build mind maps for concepts
- Visualize data/control flows
- Export as SVG or image
- Clean, professional output
- Customizable colors and styles

**Limitations:**
- Automatic placement may need tweaking
- Complex diagrams might be coarse
- Limited support for very large diagrams
- Some diagram types have layout constraints
- May need manual adjustment after generation

---

## Workflow Integration

### Planning Agent Example (Architecture)
```
Pass 1: Gather Requirements
  → Interview stakeholders
  → Document constraints
  → Identify components

Pass 2: Design Architecture
  → Use Superpowers to plan architecture details
  → Use Goodnotes to visualize design
  → Create multiple diagram views

Pass 3: Plan Implementation
  → Use Goodnotes to flowchart implementation steps
  → Create deployment diagram
  → Document critical paths

Pass 4: Publish & Document
  → Use MiniUp to publish architecture docs
  → Export diagrams for documentation
  → Update .titan with visual patterns
```

---

## Examples in Practice

### Example 1: System Architecture
```
Task: "Document Titan Zero architecture"
Query: "Use Goodnotes to create an architecture diagram showing:
- Frontend (Web/Mobile)
- API Gateway
- WorkCore service
- ZeroPay service
- Job Queue
- Database
- External integrations

Include: Component responsibilities, data flows, communication protocols"

Result: Professional architecture diagram
Export: SVG for documentation
Share: In architecture docs, onboarding materials
```

### Example 2: Job Workflow
```
Task: "Visualize job completion process"
Query: "Use Goodnotes to create a flowchart for job completion:
- Start: Staff opens job
- Action: Mark job complete
- Decision: All required fields filled?
  - No: Show error, return to job
  - Yes: Continue
- Action: Generate invoice
- Action: Send completion notification
- Decision: Customer has credits?
  - Yes: Auto-charge
  - No: Send payment reminder
- End: Job archived"

Result: Clear visual workflow
Use: Training materials, process documentation
Share: With new staff for onboarding
```

### Example 3: Sequence Diagram
```
Task: "Document payment flow"
Query: "Use Goodnotes to create a sequence diagram for payment processing:
- Actors: Customer, System, Payment Gateway
- 1. Customer initiates payment
- 2. System sends payment request
- 3. Payment Gateway processes
- 4. Gateway returns confirmation
- 5. System updates invoice status
- 6. System sends receipt
- 7. Customer receives confirmation

Include: Error scenarios, edge cases, timing"

Result: Detailed interaction diagram
Use: Developer reference, integration testing
Share: With payment team
```

### Example 4: Audit Findings
```
Task: "Visualize security audit results"
Query: "Use Goodnotes to create audit diagram showing:
- Total systems audited: 12
- Critical findings: 2 (red)
- High findings: 5 (orange)
- Medium findings: 8 (yellow)
- Low findings: 3 (blue)
- Recommendations: Security hardening roadmap

Color-code by severity, show timeline for fixes"

Result: Visual audit summary
Use: Executive reporting
Share: With security team and stakeholders
```

---

## Diagram Best Practices

1. **Clear Labels**: Every component and connection should be labeled
2. **Color Coding**: Use colors consistently (e.g., red=critical)
3. **Hierarchy**: Organize from high-level to detailed
4. **Legend**: Include legend for symbols and colors
5. **Flow Direction**: Clear entry and exit points
6. **Grouping**: Group related components
7. **Spacing**: Use whitespace effectively

---

## Common Visualization Tasks

1. **Architecture**: System components and interactions
2. **Process**: Workflows and decision flows
3. **Sequence**: Interaction timing and order
4. **Data Model**: Database schema and relationships
5. **Deployment**: Infrastructure and environments
6. **Concept Map**: Knowledge structure
7. **Timeline**: Event sequencing
8. **Comparison**: Side-by-side options

---

## Export & Sharing

**Formats:**
- **SVG**: Vector format (scalable, editable)
- **PNG/JPG**: Raster format (shareable, web-ready)
- **PDF**: Document format (printable)

**Sharing Approaches:**
- Embed in documentation
- Publish with MiniUp
- Include in architecture docs
- Add to training materials
- Share in GitHub wiki

---

## Integration with Other Tools

**Workflow:**
1. Use Superpowers to design (text-based)
2. Use Goodnotes to visualize (diagrams)
3. Use MiniUp to publish (shareable docs)
4. Use GitHub to version control (SVG in repo)

---

## Tips for Effective Use

1. **Start Conceptual**: Draw high-level first
2. **Add Details**: Expand with specific details
3. **Iterate**: Refine based on feedback
4. **Export Multiple Formats**: SVG for editing, PNG for sharing
5. **Document Legend**: Explain symbols and colors
6. **Version Diagrams**: Keep updated as architecture evolves
7. **Embed Context**: Include brief explanation with each diagram

---

## Related Tools

- **Superpowers**: Design architecture (text), use Goodnotes to visualize
- **Process Documentation AI**: Document processes, use Goodnotes to visualize
- **MiniUp**: Publish diagrams as interactive documentation
- **GitHub**: Store SVG diagrams in repository
- **Build Web Apps**: Reference diagrams in UI documentation

---

## Diagram Conventions

**Standard Symbols:**
- Rectangle: Component/Process
- Diamond: Decision
- Rounded rectangle: Start/End
- Arrow: Flow/Relationship
- Double line: Strong relationship
- Dashed line: Optional/conditional

---

**Status**: Ready to use (Free tier available)  
**Last Updated**: July 31, 2026
