# Task Queue

This directory manages incoming tasks for this agent.

## Structure

- **pending/** - Waiting tasks (not yet started)
- **active/** - Currently working tasks
- **completed/** - Finished tasks

## Task Format

Each task is a JSON file with:
```json
{
  "id": "task-uuid",
  "status": "pending|active|completed",
  "priority": "low|normal|high|critical",
  "domain": "...",
  "description": "...",
  "assigned_at": "ISO timestamp",
  "deadline": "ISO timestamp",
  "requester": "Human or Agent",
  "context": {...}
}
```

