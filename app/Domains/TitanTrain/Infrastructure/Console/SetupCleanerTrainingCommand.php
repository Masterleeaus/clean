<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Console;

use App\Domains\TitanTrain\Application\CleanerFoundationInstaller;
use App\Domains\TitanTrain\Application\CleanerOnboardingService;
use App\Domains\WorkCore\System\Context\OperationContext;
use App\Domains\WorkCore\System\Models\Company;
use App\Domains\WorkCore\System\Tenancy\TenantContext;
use App\Models\User;
use Illuminate\Console\Command;

final class SetupCleanerTrainingCommand extends Command
{
    protected $signature = 'titan-train:setup-cleaners {company} {--actor=} {--assign=} {--due-days=14}';
    protected $description = 'Install Titan Train cleaner foundation and optionally assign a cleaner.';

    public function handle(TenantContext $tenant, OperationContext $operation, CleanerFoundationInstaller $installer, CleanerOnboardingService $onboarding): int
    {
        $company = Company::withoutGlobalScopes()->where('public_id', $this->argument('company'))->orWhere('id', $this->argument('company'))->firstOrFail();
        $actor = User::query()->where('id', $this->option('actor'))->orWhere('email', $this->option('actor'))->first();
        $actor ??= User::query()->findOrFail($company->owner_user_id);
        $tenant->set((int) $company->id, (int) $actor->id);
        $operation->set((int) $company->id, (int) $actor->id);
        try {
            $program = $installer->install($actor);
            $this->info("Installed {$program->title} ({$program->public_id}).");
            if ($this->option('assign')) {
                $cleaner = User::query()->where('id', $this->option('assign'))->orWhere('email', $this->option('assign'))->firstOrFail();
                $assignment = $onboarding->assign($cleaner, $actor, null, now()->addDays((int) $this->option('due-days')));
                $this->info("Assigned {$cleaner->email} ({$assignment->public_id}).");
            }
        } finally {
            $operation->clear();
            $tenant->clear();
        }

        return self::SUCCESS;
    }
}
