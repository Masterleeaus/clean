# Build Web Apps Guide

**Tool**: Build Web Apps (Sites) - ChatGPT Plugin  
**Purpose**: Scaffold React/Next.js applications, generate responsive layouts, create dashboards  
**Best For**: Frontend-focused Code Agents, Planning Agents, Execution Agents

---

## When to Use

### Building User Interfaces
- Creating dashboards or admin panels
- Generating responsive layouts
- Building progressive web apps (PWAs)
- Prototyping UI/UX designs

### Planning Phase (Agent Pass 1-2)
- Creating UI wireframes
- Prototyping interaction patterns
- Validating design approach
- Creating clickable mockups

### Implementation Phase (Agent Pass 2-3)
- Scaffolding React components
- Generating boilerplate code
- Creating form layouts
- Building interactive features

---

## How to Use

### Scaffold React Application
```
"Use Build Web Apps to create a React dashboard with:
- Customer list with pagination
- Filtering by status
- Detail view modal
- Dark mode toggle"

"Use Build Web Apps to scaffold a Next.js admin panel with:
- Authentication page
- User management table
- Role-based navigation
- Responsive design"
```

### Generate UI Components
```
"Use Build Web Apps to generate a responsive form for [entity]"
"Use Build Web Apps to create a dashboard layout showing [metrics]"
"Use Build Web Apps to build a navigation component for [pages]"
```

### Create PWA Features
```
"Use Build Web Apps to add offline capabilities to [app]"
"Use Build Web Apps to create sync mechanisms for [data]"
"Use Build Web Apps to generate service worker boilerplate"
```

### Auto-generate Integrations
```
"Use Build Web Apps to scaffold authentication UI (login/signup)"
"Use Build Web Apps to generate API integration boilerplate"
"Use Build Web Apps to create forms that call [API endpoints]"
```

---

## Integration with Agent Workflow

### Frontend Code Agent (Pass 2)
- **Goal**: UI Implementation
- **Use Build Web Apps to**: Scaffold components, generate layouts
- **Output**: Working React components ready for customization

### Planning Agent (Pass 2)
- **Goal**: UI/UX Design
- **Use Build Web Apps to**: Create wireframes, prototype interactions
- **Output**: Visual designs, UX flow documentation

### Execution Agent (Pass 1-2)
- **Goal**: Setup & Foundation
- **Use Build Web Apps to**: Initialize project structure
- **Output**: Working development environment

---

## What Build Web Apps Generates

| Type | Examples |
|------|----------|
| **Scaffolding** | React app structure, Next.js project setup |
| **Components** | Buttons, forms, tables, cards, modals |
| **Layouts** | Dashboard grids, two-column views, responsive stacks |
| **Pages** | Login, signup, profiles, listings, detail views |
| **Features** | Pagination, search, filtering, sorting |
| **Auth UI** | Login forms, signup flows, password reset |
| **Forms** | Validation, error messages, submission |
| **Dashboards** | Charts, metrics, KPIs, status widgets |

---

## Tech Stack

- **Framework**: React 19.x or Next.js (configurable)
- **Styling**: Tailwind CSS (responsive by default)
- **Data**: SQLite for simple apps, integration points for real APIs
- **State**: Zustand or React Context depending on complexity

---

## Capabilities & Limitations

**Strengths:**
- Rapid scaffolding of working UIs
- Mobile-responsive out of the box
- Auto-generates boilerplate code
- Includes dark mode support
- Real-time preview available
- Deploy to shareable URLs

**Limitations:**
- May need manual refinement for complex interactions
- Limited private API access without guidance
- SQLite works for prototypes, not production data
- Some features may need adjustment for your brand/style

---

## Workflow Integration

### Frontend Agent Example (Code Agent, UI Focus)
```
Pass 1: Plan UI structure
  → Use GitHub to understand current UI
  → Use Goodnotes to sketch interface design

Pass 2: Build Components
  → Use Build Web Apps to scaffold React components
  → Customize generated code for your needs

Pass 3: Refine & Test
  → Use CodeRabbit to review components for quality
  → Test interactive features in preview

Pass 4: Deploy & Document
  → Use MiniUp to publish working prototype
  → Document in .titan how to extend components
```

---

## Examples in Practice

### Example 1: Dashboard Creation
```
Task: "Build customer dashboard"
Query: "Use Build Web Apps to create a React dashboard with:
- Customer list (name, email, status)
- Filter by status (active/inactive)
- Click to see details
- Add new customer button"
Result: Working dashboard with all features
Next: Customize styling, connect to real API
```

### Example 2: Admin Panel
```
Task: "Create staff portal"
Query: "Use Build Web Apps to scaffold Next.js admin with:
- Authentication page
- Staff list with search
- Role management
- Activity logs"
Result: Full admin interface skeleton
Next: Integrate with backend APIs
```

### Example 3: PWA for Mobile
```
Task: "Offline job tracking app"
Query: "Use Build Web Apps to build a React PWA with:
- Job list with offline storage
- Sync when online
- Location tracking
- Photo capture"
Result: PWA with offline capabilities
Next: Deploy and test on mobile devices
```

---

## Tips for Effective Use

1. **Be Descriptive**: Specify all features you need upfront
2. **Mobile First**: Ask for responsive/mobile-optimized designs
3. **Copy the Code**: Use generated code as starting point, customize as needed
4. **Preview First**: Test in the preview before committing
5. **Component Reuse**: Build component library from generated patterns

---

## Common Next Steps

1. **Connect to API**: Replace SQLite with real backend endpoints
2. **Add Authentication**: Integrate with your auth provider
3. **Styling**: Customize Tailwind config for brand colors
4. **Deployment**: Deploy to Vercel, Netlify, or your infrastructure
5. **Testing**: Add Playwright or Cypress tests for components

---

## Related Tools

- **GitHub**: Use to understand current UI implementation before building
- **CodeRabbit**: Review generated React components for quality
- **Goodnotes**: Design UI mockups before building
- **MiniUp**: Publish working prototypes as shareable URLs
- **Superpowers**: Plan comprehensive UI/UX before building

---

**Status**: Ready to use  
**Last Updated**: July 31, 2026
