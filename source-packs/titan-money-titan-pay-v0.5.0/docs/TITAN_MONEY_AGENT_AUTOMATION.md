# Titan Money Agent Automation

## Runtime wiring

The application registers `TitanMoneyServiceProvider`, loads the Titan Money web and API routes, registers the agent commands, and schedules the following work:

- `titan-money:agents-run` every five minutes
- `titan-money:publish-outbox` every minute
- `titan-money:dispatch-deliveries` every minute
- `titan-money:generate-recurring` hourly (queues recurring obligations for the Invoice Agent)
- `titan-money:queue-reminders` hourly

Production must run both a scheduler worker and a queue worker. Example process commands:

```bash
php artisan schedule:work
php artisan queue:work --tries=5 --timeout=120
```

## Agent roles

### Invoice Agent

Accepts company-scoped, idempotent billable events at:

```text
POST /api/titan-money/v1/billable-events
```

The event can originate from a completed WorkCore job, approved quote, recurring obligation or service agreement. The initial policy for new companies is enabled in bounded mode with an A$1,000 automatic-issue ceiling; administrators can change or disable it. A company administrator may enable automatic issue, select bounded or full autonomy, and set a maximum AUD amount. Above-limit or unapproved source types create an approval request rather than issuing.

### Receivables Agent

Reviews due and overdue issued invoices. It sends each configured stage once, marks invoices overdue only after the due date, and stops naturally when the balance reaches zero or the invoice enters a non-collectable state. The default stages are due date, 3, 7, 14 and 30 days overdue. Final notices require approval.

### Titan Pay Reconciliation Agent

Surfaces unmatched gateway events and bank deposits in the internal finance queue. It does not confirm payments, change invoice balances or bypass signed provider evidence.

## Delivery pipeline

Financial state changes are written to the transactional outbox. The publisher creates idempotent channel deliveries. Email is delivered through Laravel Mail. Customer-app and internal messages are written directly into Titan Channels. SMS, WhatsApp and voice use configured provider webhooks and authenticated delivery receipts. Reminder records advance only after a concrete transport accepts the message; missing customer routes create an internal exception.

## Required permissions

- `titanmoney.automation.read`
- `titanmoney.automation.manage`
- `titanmoney.agent.ingest`
- `titanmoney.agent.run`
- `titanmoney.agent.approve`
- existing invoice issue, void and Titan Pay permissions

## Safe activation sequence

1. Link the Titan company to WorkCore, then configure customers, consent, mail transport and Titan Pay methods.
2. Start the scheduler and queue workers.
3. Open **Titan Money → Agents**.
4. Enable Invoice Agent in observe mode and run a dry run.
5. Move to draft mode and verify generated drafts.
6. Set an AUD authority limit and enable automatic issue only after review.
7. Enable Receivables Agent in observe mode, then bounded mode.
8. Keep final notices and payment reconciliation under approval until operating evidence supports broader authority.
