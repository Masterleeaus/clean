# Meetup Titan Money + Titan Pay

This application merges the approved InvoixPro invoice capabilities into the native **Titan Money** domain and combines the approved ZeroPay payment capabilities into **Titan Pay**.

## Canonical authorities

- **Titan Money** owns invoices, receivables, GST profiles, recurring billing obligations, payments, allocations, credit notes, agent policies, approvals, financial audit records and outbox events.
- **Titan Pay** owns collection sessions, payment methods, gateway connections, signed gateway events, payment claims, protected evidence, gateway transactions and reconciliation.

## Governed agents

- **Invoice Agent** accepts idempotent WorkCore billable events, creates invoice drafts and may auto-issue only for approved source types within the company’s AUD authority limit.
- **Receivables Agent** detects due and overdue balances, sends each configured follow-up stage once, and requests approval for final notices.
- **Titan Pay Reconciliation Agent** surfaces unmatched provider events and bank deposits without marking payments verified.
- **Recurring Invoice Agent** queues recurring obligations into the same billable-event pipeline rather than bypassing invoice governance.
- **Outbox and Delivery Agents** convert financial events into idempotent email or Titan Channels delivery records.

Invoice generation and receivables follow-up start enabled in bounded mode for new companies. Payment reconciliation remains disabled/observe-only until configured.

## Install

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run build
```

Invoice issue always stores an immutable HTML document snapshot. PDF generation is optional; add it deliberately when required:

```bash
composer require barryvdh/laravel-dompdf:^3.1
```

Configure the scheduler in production:

```bash
php artisan schedule:work
```

Run a queue worker when the connected Titan Channels or mail transport uses queued jobs:

```bash
php artisan queue:work --tries=5 --timeout=120
```

## Main interfaces

- `/titan-money`
- `/titan-money/automation`
- `/titan-pay`
- `/pay/{token}` public collection page
- `/api/titan-money/v1`
- `POST /api/titan-money/v1/billable-events`
- `POST /api/titan-money/v1/agents/run`
- `/api/titan-pay/v1`
- `/api/titan-pay/webhooks/{provider}/{connection}`
- `POST /api/titan-money/v1/channel-receipts`
- Chat command: `I completed job TZ-1042. Send the invoice.`

## Agent activation

1. Install the WorkCore business-action runtime and link the Titan company to its numeric WorkCore company ID under **Titan Money → Agents**.
2. State whether WorkCore service prices already include GST before enabling automatic issue.
3. Configure PayID, bank transfer, cash and PayPal under **Titan Pay → Gateways**.
4. Review the A$1,000 initial automatic-issue limit and lower or raise it deliberately.
5. Run a dry-run agent pass and inspect its recorded decisions.
6. Configure customer channel consent and SMS/email/voice transports.
7. Keep final collection notices and payment exceptions under human approval.

See `docs/CHAT_FIRST_JOB_TO_PAYMENT.md` and `docs/TITAN_MONEY_AGENT_AUTOMATION.md` for the complete operating guide.

## Verification

```bash
php tools/tdd_core.php
php tools/tdd_agents.php
php tools/verify_titan_money_titan_pay.php
php tools/verify_agent_automation_wiring.php
find app bootstrap routes config database tests tools -name '*.php' -print0 | xargs -0 -n1 php -l
php artisan test
```

The package cannot claim full Laravel runtime verification until Composer dependencies are installed, migrations run against a disposable database, and the complete Pest suite executes.
