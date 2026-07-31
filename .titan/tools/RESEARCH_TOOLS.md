# Tavily AI Research Tools Guide

**Tool**: Tavily AI - ChatGPT Plugin  
**Purpose**: Web search, website crawling, document extraction, compliance research  
**Best For**: Research Agents, all agents needing external information

---

## When to Use

### Research & Discovery
- Finding external information for requirements
- Researching competitor solutions
- Looking up standards or best practices
- Exploring emerging technologies

### Compliance & Regulations
- Research regulatory requirements
- Find industry standards
- Lookup compliance guidelines
- Audit against external requirements

### Knowledge Gathering
- Extracting from documentation
- Scraping API specifications
- Gathering implementation examples
- Collecting best practices from community

### Competitive Analysis
- Researching competitor features
- Analyzing market approaches
- Finding alternative solutions
- Benchmarking industry standards

---

## How to Use

### Web Search
```
"Use Tavily to search for [topic] and summarize key points"
"Use Tavily to research [technology] and explain how it works"
"Use Tavily to find best practices for [technique]"
```

### Website Crawling
```
"Use Tavily to crawl [website] and extract [specific information]"
"Use Tavily to crawl the [standard body] regulations for [topic]"
"Use Tavily to scrape [API documentation site] for endpoint details"
```

### Document Research
```
"Use Tavily to find and summarize [regulatory document]"
"Use Tavily to extract [information] from [URL]"
"Use Tavily to research [topic] across multiple sources"
```

### Compliance Research
```
"Use Tavily to research [industry] compliance requirements in [region]"
"Use Tavily to find [standard name] documentation and requirements"
"Use Tavily to research data privacy regulations for [context]"
```

---

## Integration with Agent Workflow

### Research Agent (Pass 1)
- **Goal**: Initial Investigation
- **Use Tavily to**: Research scope, find related resources
- **Output**: Understanding of external landscape

### Research Agent (Pass 2)
- **Goal**: Deep Analysis
- **Use Tavily to**: Dig into specifics, find authoritative sources
- **Output**: Detailed research findings

### Code Agent (Pass 1)
- **Goal**: Investigation
- **Use Tavily to**: Research external requirements, APIs, standards
- **Output**: Context for implementation

### Planning Agent (Pass 1-2)
- **Goal**: Requirements & Architecture
- **Use Tavily to**: Research technologies, find design patterns
- **Output**: Informed architectural decisions

---

## Research Capabilities

| Capability | Examples |
|-----------|----------|
| **Web Search** | Google-like search, news articles, blog posts |
| **Crawling** | Multi-page depth, site structure exploration |
| **Extraction** | PDF text extraction, table extraction, code blocks |
| **Summarization** | Long-form summary, key points, highlights |
| **Regulation** | Compliance docs, standards, legal requirements |
| **APIs** | OpenAPI specs, SDK docs, API endpoint discovery |
| **Competitor** | Product features, pricing, capabilities analysis |

---

## Rate Limits & Pricing

- **Limit**: May have crawl limits on free tier
- **Free Tier**: Limited searches and crawls per day
- **Paid Tier**: Higher limits for heavy research
- **Setup**: Requires Tavily account connection

---

## Capabilities & Limitations

**Strengths:**
- Comprehensive web search
- Multi-page crawling capability
- Document extraction (PDFs, tables)
- Regulatory document research
- Source citation and links
- Fast summarization

**Limitations:**
- May not bypass paywalls or login walls
- Quality depends on source website structure
- Crawl depth limited (typically 5-10 pages)
- Needs manual filtering of results
- Some sites may block crawling

---

## Workflow Integration

### Research Agent Example (Compliance Audit)
```
Pass 1: Initial Investigation
  → Use Tavily to search industry compliance requirements
  → Identify applicable regulations

Pass 2: Deep Analysis
  → Use Tavily to crawl regulatory sites
  → Extract specific compliance requirements
  → Research how competitors handle compliance

Pass 3: Recommendations
  → Compile findings into framework
  → Create audit checklist
  → Document recommendations

Pass 4: Documentation
  → Use Process Documentation AI to create compliance guide
  → Update .titan with compliance patterns
```

---

## Examples in Practice

### Example 1: Compliance Research
```
Task: "Audit data privacy compliance"
Query: "Use Tavily to research GDPR requirements and extract:
- Data retention requirements
- User rights (access, deletion)
- Consent mechanisms needed
- Incident reporting procedures"
Result: Structured compliance requirements
Next: Audit current system against requirements
```

### Example 2: Technology Research
```
Task: "Evaluate AI/ML technologies"
Query: "Use Tavily to research:
- Latest LLM models and capabilities
- Comparison of Claude vs GPT vs Gemini
- Deployment options and costs
- Security and privacy considerations"
Result: Technology comparison and recommendations
Next: Make architectural decisions
```

### Example 3: API Documentation
```
Task: "Integrate with external API"
Query: "Use Tavily to crawl the [Service] API documentation and extract:
- Authentication methods
- Rate limiting
- Available endpoints
- Response formats
- Error handling"
Result: API specification
Next: Plan integration approach
```

### Example 4: Regulatory Compliance
```
Task: "Ensure environmental health compliance"
Query: "Use Tavily to crawl NSW environmental health regulations and find:
- Cleaning facility requirements
- Safety procedures
- Inspection standards
- Documentation requirements
- Compliance penalties"
Result: Compliance framework
Next: Audit facilities against standards
```

---

## Tips for Effective Use

1. **Be Specific**: Narrow search queries to targeted topics
2. **Multi-Source**: Ask Tavily to check multiple sources
3. **Verify Important Info**: Cross-check critical findings
4. **Extract Concrete**: Ask for specific data, not vague summaries
5. **Follow Links**: Ask for source URLs, verify yourself

---

## Common Research Tasks

1. **Initial Scoping**: Search topic → understand landscape → identify key areas
2. **Deep Dive**: Crawl authoritative sites → extract details → compile findings
3. **Compliance**: Find regulations → extract requirements → audit against
4. **Integration**: Find API docs → extract specs → plan integration
5. **Technology**: Compare options → evaluate features → recommend approach

---

## Search Strategy

**Effective Query Structure:**
1. **Start Broad**: General topic understanding
2. **Get Specific**: Narrow to particular requirements
3. **Go Deep**: Crawl authoritative sources
4. **Extract Details**: Pull concrete requirements or specs
5. **Compile**: Organize findings into structured format

---

## Document Types to Research

- **Regulatory**: Laws, regulations, compliance standards
- **Technical**: API docs, architecture guides, SDKs
- **Industry**: Best practices, case studies, benchmarks
- **Academic**: Research papers, standards, specifications
- **Commercial**: Competitor offerings, market analysis

---

## Related Tools

- **GitHub**: Use to understand current implementation, use Tavily to research external requirements
- **Superpowers**: Use Tavily research to inform architectural decisions
- **Process Documentation AI**: Convert research into documented procedures
- **Goodnotes**: Create diagrams from research findings

---

## Privacy & Ethics

- **Public Data**: Tavily works with publicly available information
- **Paywalls**: May not access paid/restricted content
- **Terms of Service**: Respect site ToS, crawling robots.txt
- **Attribution**: Cite sources in your findings
- **Responsible**: Use research ethically and legally

---

**Status**: Ready to use (Free tier available, rate limited)  
**Last Updated**: July 31, 2026
