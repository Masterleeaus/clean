# ⭐ PWA Specialists Guild - Best Practices

---

## 🎯 Development Standards

### Code Quality
- ✅ All code must pass linting (ESLint)
- ✅ Minimum 80% test coverage required
- ✅ Code reviews before merge
- ✅ TypeScript for type safety
- ✅ Prettier for consistent formatting

### Testing
- ✅ Unit tests for all components
- ✅ Integration tests for features
- ✅ Visual regression testing
- ✅ Responsive design testing (all breakpoints)
- ✅ Accessibility testing (aXe, WAVE)

### Performance
- ✅ Lighthouse score > 90
- ✅ Core Web Vitals compliant
- ✅ Bundle size monitoring
- ✅ Image optimization
- ✅ Code splitting for lazy loading

### Security
- ✅ No hardcoded credentials
- ✅ Multi-tenancy validation (company_id)
- ✅ Input validation & sanitization
- ✅ OWASP compliance
- ✅ Regular security audits

---

## 🎨 Design System Standards

### Design Tokens
- Use only defined design tokens
- No hardcoded colors, spacing, fonts
- Consistent naming conventions
- Documented token definitions

### Components
- Follow design system exactly
- Accessibility built-in (WCAG AA)
- Responsive by default (mobile-first)
- Support light & dark themes
- Complete prop documentation

### Accessibility
- Semantic HTML
- Keyboard navigation
- Screen reader support
- Focus indicators visible
- Sufficient color contrast (4.5:1)

---

## 🤝 Collaboration Guidelines

### Designer-Developer Communication
- Designer (Agent-21) provides detailed specs
- UI Agent (22) implements with feedback
- App agents follow implementation
- Regular design sync meetings

### Multi-App Coordination
- Share components via component library
- Consistent patterns across apps
- Coordinate on breaking changes
- Escalate conflicts to guild lead

### Knowledge Sharing
- Document decisions & rationale
- Share learnings in guild meetings
- Create reusable solutions
- Mentor newer agents

---

## 📱 Multi-Tenancy Compliance

### Every Feature Must
- Scope data by company_id
- Validate tenant access
- Prevent cross-tenant data leakage
- Log tenant-specific audits
- Support multi-company deployments

### Data Isolation
- Database queries scoped to company_id
- API responses filtered by tenant
- Caching tenant-aware
- Search/indexing tenant-scoped

---

## 📦 Deployment & DevOps

### Release Process
1. Feature complete with tests
2. Code review approved
3. Deployed to staging
4. QA testing completed
5. Merged to production
6. Deployed to production
7. Monitoring for issues

### PWA Specific
- Service worker updates tested
- Offline functionality verified
- Sync queue tested
- Device storage verified
- Cross-browser testing

---

## 📊 Metrics & Tracking

### Track Regularly
- Test coverage percentage
- Performance scores
- Accessibility compliance
- Code quality (linting errors)
- Feature completion rate
- Escalation frequency

### Monthly Review
- Performance trends
- Quality improvements
- Process bottlenecks
- Team satisfaction
- Upcoming priorities

---

## ✅ Pre-Commit Checklist

Before committing code:
- [ ] Linting passes (npm run lint)
- [ ] Tests pass (npm test)
- [ ] Test coverage > 80%
- [ ] No console errors
- [ ] Responsive design verified
- [ ] Accessibility verified (aXe)
- [ ] Performance impact assessed
- [ ] Multi-tenancy validated
- [ ] Design system followed
- [ ] Documentation updated

---

## 🚀 Performance Optimization Tips

### Bundle Size
- Use tree-shaking
- Lazy load components
- Code splitting by route
- External CDN for libraries

### Rendering
- Memoize expensive components
- Use virtualizing for lists
- Defer non-critical renders
- Profile with React DevTools

### Network
- Compress assets (gzip/brotli)
- Optimize images (WebP, AVIF)
- Service worker caching strategy
- API request batching

---

## 🔒 Security Best Practices

### Authentication & Authorization
- Validate all API requests
- Check user permissions
- Implement role-based access
- Session management

### Data Protection
- Encrypt sensitive data at rest
- Use HTTPS for all communication
- Sanitize user input
- Validate output encoding

### Audit & Logging
- Log all data modifications
- Track user actions
- Monitor for anomalies
- Maintain audit trail

---

## 📞 When to Escalate

**Escalate to Guild Lead (Agent-21):**
- Design system changes
- Breaking API changes
- Cross-app coordination needed
- Accessibility concerns
- Performance regressions

**Escalate to Coordination Agent (Agent-19):**
- Blocked by other team
- Resource constraints
- Priority conflicts
- Cross-guild coordination

**Escalate to Claude Architect:**
- Architectural decisions
- Major refactoring
- Tech stack changes
- Strategy decisions

---

**Last Updated:** 2026-07-30  
**Status:** Active  
**Owner:** PWA Specialists Guild

