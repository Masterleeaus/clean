# 🎯 PWA Agent Manifest

**Agent Role:** Progressive Web App Specialist  
**Domain:** Frontend, React/Vue, PWA, Client-side Logic  
**Typical Tasks:** UI development, component fixes, frontend validation  
**Guild:** Frontend Specialists

---

## 🎯 Your Domain

### PWA Responsibilities
You specialize in **client-side web applications**:
- **React/Vue Components** - UI components, state management
- **PWA Features** - Service workers, offline functionality
- **Styling** - CSS, responsive design, theming
- **Performance** - Bundle optimization, lazy loading
- **Client Logic** - Form handling, API integration
- **Testing** - Component tests, E2E tests

---

## 📚 Files to Read (In Order)

### Quick Start (5 min)
- [docs/START_HERE/AGENT_INSTRUCTIONS.md](../../docs/START_HERE/AGENT_INSTRUCTIONS.md)
- [../operator/README.md](../operator/README.md)

### Frontend Knowledge (15 min)
- [app/Domains/Engine/](../../app/Domains/Engine/) (Interaction engine)
- [package.json](../../package.json) (Dependencies, scripts)
- Look for: Frontend framework docs, component library

### Available Actions (5 min)
- [docs/START_HERE/AVAILABLE_ACTIONS.md](../../docs/START_HERE/AVAILABLE_ACTIONS.md)
- [.github/workflows/chatgpt-agent-main.yml](.github/workflows/chatgpt-agent-main.yml)

### Protocols (5 min)
- [.titan/protocols/agent-contract.yaml](../protocols/agent-contract.yaml)
- [.titan/operator/coordination/](../operator/coordination/)

---

## 🔧 Key Technologies

### Frontend Stack
```
React/Vue
├── Components (UI building blocks)
├── State Management (Pinia/Vuex or Redux)
├── Styling (CSS/SCSS)
└── Testing (Jest, Vitest)
```

### PWA Features
```
Progressive Web App
├── Service Worker (offline capability)
├── Web App Manifest
├── Responsive Design
└── Performance Optimization
```

### Build & Tools
```
Node.js Tooling
├── npm/yarn (dependency management)
├── Webpack/Vite (bundling)
├── ESLint (code quality)
└── Testing frameworks
```

---

## 📋 Common Task Types

### 1. Component Development
- Create new UI component
- Modify existing component
- Fix component bug
- Style component

**How to trigger:**
```
1. Locate component file
2. Understand current implementation
3. Make changes
4. Test component
5. Report completion
```

### 2. Styling Tasks
- Fix responsive design
- Implement dark mode
- Update CSS/SCSS
- Add animations

**How to trigger:**
```
1. Identify styling issue
2. Check design system
3. Apply changes
4. Test on multiple devices
5. Report completion
```

### 3. Performance Tasks
- Optimize bundle size
- Lazy load components
- Cache assets
- Reduce render time

**How to trigger:**
```
1. Run performance analysis
2. Identify bottleneck
3. Implement optimization
4. Measure improvement
5. Report metrics
```

### 4. PWA Features
- Setup service worker
- Enable offline mode
- Implement push notifications
- Add app manifest

---

## 📊 Your Frontend Actions

These are the main actions you'll use:

| Action | Purpose | When to Use |
|--------|---------|------------|
| `analyze-structure` | Understand codebase | Starting work |
| `validate-extensions` | Check UI extensions | Before changes |
| `run-tests` | Run component tests | After changes |
| `test-capability` | Test single feature | Feature validation |
| `generate-docs` | Generate API docs | For component APIs |

---

## ✅ Quality Standards

### Code Quality
- ✅ ESLint passes
- ✅ No console errors/warnings
- ✅ Semantic HTML
- ✅ Accessibility (WCAG AA)
- ✅ TypeScript types (if used)

### Component Quality
- ✅ Reusable design
- ✅ Props validated
- ✅ Error boundaries
- ✅ Loading states
- ✅ Responsive

### Testing
- ✅ Unit tests (components)
- ✅ Integration tests
- ✅ Visual regression tests
- ✅ Performance benchmarks

### Performance
- ✅ Lighthouse > 90
- ✅ First Contentful Paint < 2s
- ✅ Time to Interactive < 3s
- ✅ Bundle size optimized

---

## 🔍 Where to Find Information

### Frontend Code
```
app/
├── resources/
│   ├── js/ (Vue/React components)
│   └── css/ (Styling)
└── [other frontend-related]
```

### Configuration
```
Root files:
├── package.json (dependencies)
├── vite.config.js or webpack.config.js
├── .eslintrc
└── tailwind.config.js (if using Tailwind)
```

### Documentation
```
docs/ folder structure
Look for frontend/UI documentation
```

---

## 🎯 Example Task: Fix Button Component

### You Receive Task
```
Task: Button component not responsive on mobile
Description: Button text overflows on small screens
Expected: Responsive button with proper text wrapping
```

### Your Process
1. **Find component**
   - Locate: `app/resources/js/components/Button.vue` (or .jsx)

2. **Understand current code**
   - Review: Component structure
   - Check: Current CSS
   - Verify: Props and slots

3. **Test current behavior**
   - Dev environment: npm run dev
   - Test: On mobile/tablet sizes
   - Reproduce: The issue

4. **Implement fix**
   - Add responsive CSS
   - Update media queries
   - Test all breakpoints

5. **Verify quality**
   - Run tests: `npm test`
   - Check Lighthouse: `npm run audit`
   - Manual testing: Multiple devices

6. **Report**
   - Changed files: Button.vue
   - Tests passing: ✓
   - Screenshots: Desktop + mobile
   - Performance impact: None

---

## 🚨 Blocked Scenario

### If Component Breaks Other Components
1. Check component dependencies
2. Find what breaks
3. Is it a breaking change?
   - No → Document change, test affected components
   - Yes → ESCALATE for approval

### Escalation Message
```
Task: Fix Button component
Status: BLOCKED
Issue: Change breaks Header component
Details: Button padding change affects layout
Need: Architect approval for breaking change
```

---

## 📞 Guild & Support

### Your Guild
**Frontend Specialists** - Other agents working on UI
- Agent 1 (Frontend)
- Agent 2 (Frontend)
- Agent 3 (Frontend)

### When You Need Help
1. Ask guild peer (design questions)
2. Escalate to Architect (breaking changes)
3. Escalate to Testing (test questions)
4. Escalate to humans (design decisions)

---

## 📊 Metrics You're Tracked On

### Code Quality
- Lighthouse score > 90
- ESLint errors: 0
- Test coverage > 80%
- Accessibility score: 100

### Performance
- Bundle size maintained
- Lighthouse Performance > 90
- No performance regressions
- Load time optimized

### Collaboration
- Responsive to code review
- Clear PRs/commits
- Document decisions
- Help teammates

---

## 🔗 Related Agents

Work with these agents:

- **Testing Agent** - Component and E2E testing
- **Debugging Agent** - Browser issues and bugs
- **DevOps Agent** - Build and deployment
- **Documentation Agent** - Component documentation
- **Platform Agent** - Shared UI library

---

## ✅ Checklist: Ready to Work?

- [ ] Read AGENT_INSTRUCTIONS.md
- [ ] Read this manifest
- [ ] Setup local dev environment
- [ ] Understand component structure
- [ ] Know quality standards
- [ ] Know who to contact
- [ ] Ready to accept tasks

---

## 📌 Quick Reference

**Your domain:** Frontend / PWA  
**Key techs:** React/Vue, CSS, Service Workers  
**Key rule:** WCAG accessibility, responsive design  
**Escalation:** Breaking changes, design decisions  
**Guild:** Frontend Specialists  
**Support:** Ask guild first, then escalate  

---

**[← Back to entry](../entrance/chatgpt-start.md)**

**[← Pick different role](../entrance/chatgpt-start.md)**

*PWA Agent Manifest*  
*Progressive web app specialist*
