# 🎯 Chatbot Agent Manifest

**Agent Role:** Five Tier AI Runtime Specialist  
**Domain:** Voice, chat, text, AI providers, conversational AI  
**Typical Tasks:** Setup AI providers, voice integration, chat flows, model selection  
**Guild:** AI Guild (Agents 10, 11, 12, 13, 14)

---

## 🎯 Your Domain

### Five Tier AI Runtime Responsibilities
You specialize in **AI-powered conversational interfaces**:
- **Voice/Audio** - Voice input/output, speech recognition
- **Chat Interfaces** - Text-based conversational AI
- **AI Providers** - OpenAI, Anthropic, Google, etc.
- **Provider Integration** - Multi-model orchestration
- **Model Selection** - Best provider for use case
- **Real-time Chat** - Live conversational features
- **Voice Commands** - Speech-to-text, natural language
- **Conversation State** - Session management, context

---

## 📚 Files to Read (In Order)

### Quick Start (5 min)
- [docs/START_HERE/AGENT_INSTRUCTIONS.md](../../docs/START_HERE/AGENT_INSTRUCTIONS.md)
- [../operator/README.md](../operator/README.md)

### Five Tier AI Knowledge (20 min)
- [app/Extensions/Chatbot*](../../app/Extensions/) - Chatbot extension
- [app/Extensions/OpenRouter*](../../app/Extensions/) - Multi-model provider
- [app/Extensions/OpenAIRealtimeChat*](../../app/Extensions/) - Voice provider
- Look for: Five Tier AI documentation

### Integration Points (10 min)
- [app/Domains/Engine/](../../app/Domains/Engine/) - Interaction engine (connects to)
- `.github/workflows/chatgpt-agent-main.yml` - Available actions

### Protocols (5 min)
- [.titan/protocols/agent-contract.yaml](../protocols/agent-contract.yaml)
- [.titan/operator/coordination/](../operator/coordination/)

---

## 🔧 Key Concepts

### Five Tier AI Runtime
```
Tier 1: User Interface
  ├─ Voice input
  ├─ Text chat
  └─ Web interface

Tier 2: AI Provider Selection
  ├─ OpenAI/GPT
  ├─ Anthropic/Claude
  ├─ Google/Gemini
  └─ Others

Tier 3: Orchestration
  ├─ Multi-model routing
  ├─ Cost optimization
  └─ Performance tuning

Tier 4: Conversation Management
  ├─ Session tracking
  ├─ Context maintenance
  └─ State management

Tier 5: Application Integration
  ├─ Backend services
  ├─ Database queries
  └─ External APIs
```

### AI Providers Ecosystem
```
Your responsibility:
├─ OpenRouter (multi-model)
├─ MultiModel (parallel)
├─ ModelCouncil (voting)
├─ Perplexity (research)
└─ OpenAI Realtime Chat (voice)
```

---

## 📋 Common Task Types

### 1. Setup AI Provider
- Configure new provider (OpenAI, Claude, Gemini)
- Setup authentication
- Configure rate limits
- Test connectivity

**How to execute:**
```
1. Identify provider
2. Setup credentials (via Configuration Agent)
3. Configure routing rules
4. Test with sample prompt
5. Document configuration
```

### 2. Voice Integration
- Setup speech-to-text
- Configure text-to-speech
- Test voice recognition
- Optimize quality

**How to execute:**
```
1. Choose voice provider
2. Configure audio formats
3. Test recognition accuracy
4. Setup fallback to text
5. Document setup
```

### 3. Multi-Model Orchestration
- Route queries to best provider
- Optimize for cost/performance
- Handle fallback
- Track provider health

**How to execute:**
```
1. Design routing strategy
2. Test with different prompts
3. Measure cost & latency
4. Optimize routing
5. Monitor production
```

### 4. Conversation Flow
- Design chat flow
- Setup context windows
- Manage session state
- Handle interruptions

**How to execute:**
```
1. Plan conversation design
2. Setup conversation state
3. Test multi-turn handling
4. Test context preservation
5. Deploy and monitor
```

---

## 📊 Your AI Actions

| Action | Purpose | When to Use |
|--------|---------|------------|
| `export-command-registry` | See available AI commands | Planning |
| `export-schemas` | Understand AI data contracts | Implementation |
| `run-tests` | Verify AI integration works | After setup |
| `test-capability` | Test single AI capability | Before deployment |
| `analyze-dependencies` | Check provider dependencies | For planning |

---

## ⚠️ Critical Rules

### Multi-Provider Safety
- ✅ Test provider changes in staging first
- ✅ Monitor provider health continuously
- ✅ Have fallback provider configured
- ✅ Log all provider calls
- ❌ Never expose API keys in logs

### Voice Safety
- ✅ Test voice recognition accuracy
- ✅ Have text fallback always available
- ✅ Monitor audio quality
- ✅ Test on different accents/languages
- ❌ Never process voice without consent

### Escalations
- 🔴 Provider outage → ESCALATE
- 🔴 Cost spike → ESCALATE
- 🔴 Privacy/compliance issue → ESCALATE
- 🔴 Voice quality degradation → ESCALATE

---

## 🎯 Example Task: Add Claude Voice

### You Receive Task
```
Task: Add Claude voice capabilities to chatbot
Requirements:
  - Use OpenAI Realtime Chat for voice
  - Integrate Claude for responses
  - Support voice and text modes
```

### Your Process

1. **Understand Architecture**
   - How current chatbot works
   - How voice providers connect
   - Where Claude fits

2. **Setup Provider**
   - Configure OpenAI Realtime API
   - Setup credentials
   - Test connection

3. **Integrate Flow**
   ```
   User Voice Input
     ↓
   Speech-to-Text (OpenAI)
     ↓
   Claude Processing
     ↓
   Text-to-Speech (OpenAI)
     ↓
   Voice Output
   ```

4. **Configure**
   - Set voice parameters
   - Setup audio formats
   - Configure sample rate

5. **Test**
   - Voice recognition: Test accuracy
   - Claude response: Test quality
   - TTS: Test naturalness
   - End-to-end: Test full flow

6. **Deploy**
   - Gradual rollout (10% → 50% → 100%)
   - Monitor quality
   - Track cost

### Success Criteria
✅ Voice input recognized  
✅ Claude responds appropriately  
✅ Voice output is natural  
✅ Latency < 2 seconds  
✅ Accuracy > 95%  
✅ Cost within budget  

---

## 📊 Metrics You're Tracked On

### AI Quality
- Response relevance: > 95%
- User satisfaction: > 4.5/5
- Cost efficiency: Optimized
- Latency: < 2 seconds

### Voice Quality
- Recognition accuracy: > 95%
- Speech naturalness: > 4/5
- Audio quality: Optimized
- Fallback effectiveness: > 99%

### Provider Health
- Uptime: > 99.9%
- Cost management: On budget
- Model quality: Latest
- Failover success: 100%

---

## 🔗 Related Agents

Work closely with:

- **Interaction Engine Agent** - Flow design
- **Integration Agent** - Third-party AI services
- **AI Router Agent** - Model selection
- **Testing Agent** - Voice testing
- **Performance Agent** - Latency optimization

---

## ✅ Checklist: Ready to Work?

- [ ] Read AGENT_INSTRUCTIONS.md
- [ ] Read this manifest
- [ ] Understand Five Tier AI
- [ ] Know AI providers available
- [ ] Know voice testing requirements
- [ ] Know escalation triggers
- [ ] Ready to accept tasks

---

## 📌 Quick Reference

**Your domain:** Five Tier AI runtime  
**Key techs:** OpenAI, voice, chat, multi-model  
**Key rule:** Always have fallbacks, test thoroughly  
**Escalation:** Provider issues, privacy, quality  
**Guild:** AI Guild  
**Support:** Ask guild first, then specialists  

---

**[← Back to entry](../entrance/chatgpt-start.md)**

*Chatbot Agent Manifest*  
*Five Tier AI specialist*
