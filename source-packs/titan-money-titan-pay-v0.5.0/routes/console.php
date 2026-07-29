<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$agentMinutes = min(59, max(1, (int) config('titanmoney.automation.agent_schedule_minutes', 5)));
$outboxLimit = max(1, (int) config('titanmoney.automation.outbox_batch_size', 200));
$deliveryLimit = max(1, (int) config('titanmoney.automation.delivery_batch_size', 100));

Schedule::command('titan-money:generate-recurring')->hourly()->withoutOverlapping();
Schedule::command('titan-money:queue-reminders')->hourly()->withoutOverlapping();
Schedule::command('titan-money:agents-run')->cron("*/{$agentMinutes} * * * *")->withoutOverlapping();
Schedule::command("titan-money:publish-outbox --limit={$outboxLimit}")->everyMinute()->withoutOverlapping();
Schedule::command("titan-money:dispatch-deliveries --limit={$deliveryLimit}")->everyMinute()->withoutOverlapping();
