# Titan Train chatbot PWA integration

Titan Train is server-authoritative and online-only in Pass 2. The chatbot PWA loads:

- `/chatbot-pwa/apps/titan-train.json`
- `/chatbot-pwa/apps/titan-train.js`

The bridge sends the authenticated bearer token and `X-Titan-Company` header to the MagicAI host. It exposes:

```js
TitanTrain.client.bootstrap()
TitanTrain.client.assignments()
TitanTrain.client.assignment(publicId)
TitanTrain.client.completeLesson(assignmentId, lessonId)
TitanTrain.client.startAssessment(assignmentId, assessmentId)
TitanTrain.client.submitAttempt(attemptId, answers)
```

No Titan Train API response is cached by this bridge.
