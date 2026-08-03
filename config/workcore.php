<?php

declare(strict_types=1);

$module = static function (string $key, array $permissions, array $extra = []): array {
    return array_replace_recursive([
        'routes_enabled' => false,
        'route_prefix' => "api/workcore/{$key}",
        'middleware' => ['api', 'auth:sanctum', 'titan.company'],
        'permissions' => $permissions,
    ], $extra);
};

return [
    'version' => '0.4.0',
    'enabled' => env('WORKCORE_ENABLED', true),
    'require_tenant' => true,
    'identity' => [
        'user_model' => App\Models\User::class,
        'company_model' => App\Models\Company::class,
        'active_company_column' => 'active_company_id',
    ],
    'tenancy' => [
        'header' => env('TITAN_COMPANY_HEADER', 'X-Titan-Company'),
        'session_key' => env('TITAN_COMPANY_SESSION_KEY', 'titan_company_id'),
        'routes_enabled' => false,
    ],
    'context' => [
        'default_locale' => 'en_AU',
        'default_timezone' => 'Australia/Melbourne',
        'default_currency' => 'AUD',
        'default_country' => 'AU',
    ],
    'permissions' => ['allow_platform_fallback' => false],
    'outbox' => [
        'enabled' => true,
        'max_attempts' => 10,
        'retry_seconds' => 60,
        'consumers' => [],
    ],
    'capabilities' => [
        'workcore.core', 'workcore.crm', 'workcore.premises', 'workcore.catalogue',
        'workcore.operations', 'workcore.scheduling', 'workcore.dispatch', 'workcore.workforce',
        'workcore.rosters', 'workcore.assets', 'workcore.inventory', 'workcore.forms',
        'workcore.attendance', 'workcore.support', 'workcore.knowledge', 'workcore.reviews',
        'workcore.supply', 'workcore.fleet', 'workcore.repairs', 'workcore.trade_compliance',
        'workcore.documents', 'workcore.assurance', 'workcore.accommodation',
        'workcore.finance', 'workcore.payments', 'workcore.trust_accounting',
    ],
    'crm' => $module('crm', [
        'customers' => ['view' => 'workcore.crm.customers.view', 'create' => 'workcore.crm.customers.create'],
        'contacts' => ['create' => 'workcore.crm.contacts.create', 'update' => 'workcore.crm.contacts.update'],
        'leads' => [
            'view' => 'workcore.crm.leads.view', 'create' => 'workcore.crm.leads.create',
            'update' => 'workcore.crm.leads.update', 'convert' => 'workcore.crm.leads.convert',
        ],
    ]),
    'premises' => $module('premises', [
        'view' => 'workcore.premises.view', 'create' => 'workcore.premises.create',
        'approve' => 'workcore.premises.approve', 'archive' => 'workcore.premises.archive',
        'manage_spaces' => 'workcore.premises.spaces.manage',
        'manage_hazards' => 'workcore.premises.hazards.manage',
        'manage_access' => 'workcore.premises.access.manage',
        'manage_service_plans' => 'workcore.premises.service_plans.manage',
        'record_meter_readings' => 'workcore.premises.meter_readings.record',
        'manage_identifiers' => 'workcore.premises.identifiers.manage',
        'manage_contacts' => 'workcore.premises.contacts.manage',
        'manage_documents' => 'workcore.premises.documents.manage',
        'manage_party_roles' => 'workcore.premises.party_roles.manage',
        'manage_agreements' => 'workcore.premises.agreements.manage',
        'manage_occupancies' => 'workcore.premises.occupancies.manage',
    ]),
    'accommodation' => $module('accommodation', [
        'view' => 'workcore.accommodation.view',
        'manage_rates' => 'workcore.accommodation.rates.manage',
        'manage_reservations' => 'workcore.accommodation.reservations.manage',
        'check_in' => 'workcore.accommodation.check_in',
        'check_out' => 'workcore.accommodation.check_out',
        'housekeeping' => 'workcore.accommodation.housekeeping',
        'charges' => 'workcore.accommodation.charges',
    ]),
    'finance' => $module('finance', [
        'view' => 'workcore.finance.view',
        'create_quotes' => 'workcore.finance.quotes.create',
        'manage_quotes' => 'workcore.finance.quotes.manage',
        'create_invoices' => 'workcore.finance.invoices.create',
        'issue_invoices' => 'workcore.finance.invoices.issue',
        'manage_invoices' => 'workcore.finance.invoices.manage',
        'credit_notes' => 'workcore.finance.credit_notes.manage',
        'expenses' => 'workcore.finance.expenses.record',
        'approve_expenses' => 'workcore.finance.expenses.approve',
        'allocate_receivables' => 'workcore.finance.receivables.allocate',
        'manage_accounts' => 'workcore.finance.accounts.manage',
        'manage_periods' => 'workcore.finance.periods.manage',
        'post_journals' => 'workcore.finance.journals.post',
    ]),
    'payments' => $module('payments', [
        'view' => 'workcore.payments.view',
        'create_sessions' => 'workcore.payments.sessions.create',
        'record_attempts' => 'workcore.payments.attempts.record',
        'record_observations' => 'workcore.payments.observations.record',
        'match' => 'workcore.payments.matches.manage',
        'reconcile' => 'workcore.payments.reconcile',
    ]),
    'trust_accounting' => $module('trust_accounting', [
        'view' => 'workcore.trust_accounting.view',
        'manage_accounts' => 'workcore.trust_accounting.accounts.manage',
        'manage_matters' => 'workcore.trust_accounting.matters.manage',
        'record_receipts' => 'workcore.trust_accounting.receipts.record',
        'allocate_receipts' => 'workcore.trust_accounting.receipts.allocate',
        'request_disbursements' => 'workcore.trust_accounting.disbursements.request',
        'approve_disbursements' => 'workcore.trust_accounting.disbursements.approve',
        'release_disbursements' => 'workcore.trust_accounting.disbursements.release',
        'reverse_ledger' => 'workcore.trust_accounting.ledger.reverse',
    ]),
    'catalogue' => $module('catalogue', ['manage' => 'workcore.catalogue.manage']),
    'operations' => $module('operations', [
        'view' => 'workcore.operations.view', 'create' => 'workcore.operations.create',
        'status' => 'workcore.operations.status', 'tasks_update' => 'workcore.operations.tasks.update',
        'time_entries_create' => 'workcore.operations.time_entries.create',
    ]),
    'scheduling' => $module('scheduling', [
        'view' => 'workcore.scheduling.view', 'create' => 'workcore.scheduling.create',
        'update' => 'workcore.scheduling.update', 'status' => 'workcore.scheduling.status',
    ], ['conflicts' => ['prevent_premises_overlap_by_default' => true]]),
    'dispatch' => $module('dispatch', [
        'view' => 'workcore.dispatch.view', 'assign' => 'workcore.dispatch.assign',
        'reassign' => 'workcore.dispatch.reassign', 'status' => 'workcore.dispatch.status',
    ]),
    'workforce' => $module('workforce', [
        'manage' => 'workcore.workforce.manage', 'skills' => 'workcore.workforce.skills',
        'certifications' => 'workcore.workforce.certifications',
    ]),
    'rosters' => $module('rosters', [
        'availability' => 'workcore.rosters.availability', 'manage' => 'workcore.rosters.manage',
        'assign' => 'workcore.rosters.assign', 'publish' => 'workcore.rosters.publish',
    ], ['ui_routes_enabled' => false, 'ui_route_prefix' => 'workcore/rosters', 'ui_middleware' => ['web', 'auth', 'titan.company']]),
    'assets' => $module('assets', [
        'view' => 'workcore.assets.view', 'manage' => 'workcore.assets.manage',
        'custody' => 'workcore.assets.custody', 'maintenance' => 'workcore.assets.maintenance',
        'identifiers' => 'workcore.assets.identifiers',
    ], ['schedule_due_processing' => false, 'schedule_overdue_custody_processing' => false, 'warranty_expiry_warning_days' => 30]),
    'inventory' => $module('inventory', [
        'manage' => 'workcore.inventory.manage', 'locations' => 'workcore.inventory.locations',
        'movements' => 'workcore.inventory.movements', 'reserve' => 'workcore.inventory.reserve',
        'consume' => 'workcore.inventory.consume',
    ]),
    'forms' => $module('forms', [
        'manage_templates' => 'workcore.forms.templates.manage', 'publish_templates' => 'workcore.forms.templates.publish',
        'complete' => 'workcore.forms.complete', 'submit' => 'workcore.forms.submit',
        'convert_failures' => 'workcore.forms.failures.convert',
    ]),
    'attendance' => $module('attendance', [
        'clock' => 'workcore.attendance.clock', 'timesheets_submit' => 'workcore.attendance.timesheets.submit',
        'timesheets_approve' => 'workcore.attendance.timesheets.approve', 'leave_manage' => 'workcore.attendance.leave.manage',
        'leave_request' => 'workcore.attendance.leave.request', 'leave_approve' => 'workcore.attendance.leave.approve',
    ], ['rules' => ['prevent_dispatch_during_approved_leave' => true, 'prevent_roster_assignment_during_approved_leave' => true]]),
    'support' => $module('support', [
        'manage_queues' => 'workcore.support.queues.manage', 'create' => 'workcore.support.create',
        'reply' => 'workcore.support.reply', 'assign' => 'workcore.support.assign',
        'status' => 'workcore.support.status', 'convert_to_work_order' => 'workcore.support.convert_to_work_order',
    ]),
    'knowledge' => $module('knowledge', [
        'manage_categories' => 'workcore.knowledge.categories.manage', 'create' => 'workcore.knowledge.create',
        'edit' => 'workcore.knowledge.edit', 'publish' => 'workcore.knowledge.publish',
        'feedback' => 'workcore.knowledge.feedback',
    ]),
    'reviews' => $module('reviews', [
        'request' => 'workcore.reviews.request', 'record' => 'workcore.reviews.record',
        'publish' => 'workcore.reviews.publish',
        'recovery' => 'workcore.reviews.recovery', 'rebook' => 'workcore.reviews.rebook',
    ]),
    'supply' => $module('supply', [
        'view' => 'workcore.supply.view', 'manage_catalogue' => 'workcore.supply.catalogue.manage',
        'manage_suppliers' => 'workcore.supply.suppliers.manage', 'purchase' => 'workcore.supply.purchase',
        'approve' => 'workcore.supply.approve', 'receive' => 'workcore.supply.receive',
        'transfer' => 'workcore.supply.transfer', 'count' => 'workcore.supply.count',
        'adjust' => 'workcore.supply.adjust', 'approve_adjustment' => 'workcore.supply.adjust.approve',
    ]),
    'fleet' => $module('fleet', [
        'view' => 'workcore.fleet.view', 'manage' => 'workcore.fleet.manage',
        'mileage' => 'workcore.fleet.mileage', 'assign' => 'workcore.fleet.assign',
    ]),
    'repairs' => $module('repairs', [
        'view' => 'workcore.repairs.view', 'manage_templates' => 'workcore.repairs.templates.manage',
        'report' => 'workcore.repairs.report', 'manage' => 'workcore.repairs.manage',
        'return_to_service' => 'workcore.repairs.return_to_service',
    ]),
    'documents' => $module('documents', [
        'view' => 'workcore.documents.view', 'create' => 'workcore.documents.create',
        'version' => 'workcore.documents.version', 'link' => 'workcore.documents.link',
        'comment' => 'workcore.documents.comment', 'approve' => 'workcore.documents.approve',
        'evidence' => 'workcore.documents.evidence', 'signoff' => 'workcore.documents.signoff',
    ]),
    'assurance' => $module('assurance', [
        'view' => 'workcore.assurance.view', 'templates' => 'workcore.assurance.templates',
        'inspect' => 'workcore.assurance.inspect', 'complete' => 'workcore.assurance.complete',
        'findings' => 'workcore.assurance.findings', 'corrective_actions' => 'workcore.assurance.corrective_actions',
        'risks' => 'workcore.assurance.risks', 'incidents' => 'workcore.assurance.incidents',
    ]),
    'trade_compliance' => [
        'permissions' => ['view' => 'workcore.trade_compliance.view'],
    ],
    'modules' => ['crm' => ['enabled' => true]],
    'ndis' => ['permissions' => ['view' => 'workcore.ndis.view', 'claims' => 'workcore.ndis.claims', 'restricted_notes' => 'workcore.ndis.restricted_notes']],
];
