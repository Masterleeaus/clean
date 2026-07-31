# MiniUp Publishing Guide

**Tool**: MiniUp - ChatGPT Plugin  
**Purpose**: Quickly publish static sites, create APIs from data, host datasets, publish utilities  
**Best For**: Frontend Agents, Execution Agents, all agents needing quick publishing

---

## When to Use

### Publishing Documentation
- Publishing generated API documentation
- Hosting team wikis or guides
- Sharing knowledge base articles
- Publishing deployment runbooks

### Sharing Prototypes
- Publishing UI mockups quickly
- Sharing working demos
- Hosting proof-of-concept implementations
- Creating shareable links for feedback

### Data Hosting
- Publishing CSV/JSON datasets
- Creating queryable data APIs
- Hosting public reference data
- Sharing configuration guides

### Utilities & Tools
- Publishing standalone calculators or tools
- Hosting configuration generators
- Sharing data converters
- Publishing generated dashboards

---

## How to Use

### Publish Static Site
```
"Use MiniUp to publish this HTML dashboard as a live website and give me the URL"
"Use MiniUp to publish the API documentation site"
"Use MiniUp to host this React SPA at a public URL"
```

### Create Data API
```
"Use MiniUp to convert this CSV to a queryable JSON API"
"Use MiniUp to host this equipment register data as a REST API"
"Use MiniUp to create an API endpoint for this data table"
```

### Publish Generated Content
```
"Use MiniUp to publish the deployment checklist as an interactive page"
"Use MiniUp to publish this knowledge base as a searchable wiki"
"Use MiniUp to host the customer onboarding guide"
```

### Share Utilities
```
"Use MiniUp to publish this quote calculator as a shareable tool"
"Use MiniUp to publish the configuration generator"
"Use MiniUp to host this data analysis tool"
```

---

## Integration with Agent Workflow

### Execution Agent (Pass 3)
- **Goal**: Verification & Polish
- **Use MiniUp to**: Publish documentation, share prototypes
- **Output**: Live shareable links for review

### Frontend Agent (Pass 2-3)
- **Goal**: Build & Verify UI
- **Use MiniUp to**: Publish working prototype
- **Output**: Live URL for stakeholder feedback

### Research Agent (Pass 3-4)
- **Goal**: Share Findings
- **Use MiniUp to**: Publish audit reports or analysis dashboards
- **Output**: Interactive findings documentation

### Planning Agent (Pass 3-4)
- **Goal**: Share Plans
- **Use MiniUp to**: Publish architecture documentation
- **Output**: Interactive diagrams and specifications

---

## What MiniUp Supports

| Type | Examples |
|------|----------|
| **Static Sites** | HTML, CSS, JS single-page apps |
| **SPAs** | React apps, Vue apps, Angular apps |
| **Documentation** | Markdown rendered, HTML docs, wikis |
| **Dashboards** | Data visualizations, metric displays |
| **Data APIs** | CSV/JSON converted to REST endpoints |
| **Tools** | Calculators, converters, generators |
| **Archives** | Upload ZIP files, auto-extract |
| **Datasets** | Public data, reference tables |

---

## Deployment Model

**How It Works:**
1. Upload content (static files, CSV, ZIP)
2. MiniUp hosts it at a public URL
3. Share the URL with stakeholders
4. Content is live and accessible
5. Can update by re-uploading

**Access:**
- Public URLs (shareable links)
- No authentication required
- No login walls
- Immediate availability

---

## Capabilities & Limitations

**Strengths:**
- Instant publishing without deployment
- No server setup needed
- Perfect for prototypes and demos
- Shareable URLs
- Supports various content types
- Quick iteration

**Limitations:**
- Read-only after publish (no real-time updates)
- No authentication or login
- No database (static/data-only)
- Basic features (no complex backend logic)
- Temporary URLs may expire (check duration)

---

## Workflow Integration

### Frontend Agent Example (Publishing)
```
Pass 2: Build UI
  → Use Build Web Apps to scaffold dashboard
  → Customize components

Pass 3: Publish for Review
  → Export working app
  → Use MiniUp to publish as live URL
  → Share with stakeholders for feedback

Pass 4: Iterate & Deploy
  → Incorporate feedback
  → Redeploy with MiniUp or to production
```

---

## Examples in Practice

### Example 1: Publish Documentation
```
Task: "Share API documentation"
Generated: HTML docs from OpenAPI spec
Query: "Use MiniUp to publish this API documentation as a live website"
Result: Live URL like https://miniup.io/docs-xyz123
Share: Send link to team/customers
```

### Example 2: Prototype Dashboard
```
Task: "Get feedback on dashboard design"
Built: React dashboard with sample data
Query: "Use MiniUp to publish this React dashboard"
Result: Live interactive dashboard at shareable URL
Feedback: Share with stakeholders for validation
```

### Example 3: Data API
```
Task: "Share equipment registry"
Have: equipment.csv with 500+ items
Query: "Use MiniUp to convert this CSV to a queryable JSON API"
Result: REST API at live URL (GET /equipment, /equipment/{id}, etc)
Usage: Reference data available for other systems
```

### Example 4: Interactive Guide
```
Task: "Share deployment procedures"
Created: HTML checklist with styling
Query: "Use MiniUp to publish this interactive deployment checklist"
Result: Live checklist at shareable URL
Distribution: Link in runbooks, share with ops team
```

---

## Tips for Effective Use

1. **Keep It Simple**: Static content works best
2. **Pre-generate**: Create complete content before publishing
3. **Version**: Include version numbers in URLs
4. **Share Carefully**: Public URLs are world-readable
5. **Check Duration**: Verify URL expiration policy
6. **Document**: Note where published URLs are referenced

---

## Common Publishing Tasks

1. **First Publish**: Prepare content → Upload to MiniUp → Get URL → Share
2. **Updates**: Modify content locally → Re-upload → New URL generated
3. **Archiving**: Create ZIP of all files → Upload → Get archive URL
4. **Data Sharing**: Export CSV → Upload to MiniUp → Get data API
5. **Documentation**: Generate HTML → Publish → Share in runbooks

---

## Content Preparation

**For Websites:**
- Ensure index.html exists
- Bundle all assets (CSS, JS, images)
- Test locally first
- Keep file sizes reasonable

**For Data APIs:**
- Ensure CSV headers are clear
- Clean data (no special characters)
- Include descriptions/metadata
- Test API responses

**For Utilities:**
- Package as single HTML file (no external deps)
- Include instructions/usage guide
- Test all functionality
- Ensure responsive design

---

## Related Tools

- **Build Web Apps**: Generate UIs to publish with MiniUp
- **Goodnotes**: Create diagrams → export → publish with MiniUp
- **Process Documentation AI**: Generate docs → publish with MiniUp
- **Manufact**: For backend services (MiniUp is for static only)

---

## Security Considerations

- **Public URLs**: Assume world-readable
- **No Secrets**: Never publish with API keys or passwords
- **Data Privacy**: Don't publish PII without consideration
- **HTTPS**: URLs are HTTPS secured
- **Duration**: Confirm URL retention policy with MiniUp

---

**Status**: Ready to use (Free tier available)  
**Last Updated**: July 31, 2026
