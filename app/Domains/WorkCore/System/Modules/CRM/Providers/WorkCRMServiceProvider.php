<?php

declare(strict_types=1);
namespace App\Domains\WorkCore\System\Modules\CRM\Providers;
use App\Domains\WorkCore\System\Actions\{ActionDefinition,BusinessActionRegistry};
use App\Domains\WorkCore\System\Modules\CRM\Actions\{ConvertLeadToCustomer,CreateContact,CreateCustomer,CreateLead,SearchCustomers,SearchLeads,SetPrimaryContact,UpdateLead};
use App\Domains\WorkCore\System\Modules\CRM\Contracts\{ContactRepositoryContract,CustomerRepositoryContract,LeadRepositoryContract};
use App\Domains\WorkCore\System\Modules\CRM\Repositories\{EloquentContactRepository,EloquentCustomerRepository,EloquentLeadRepository};
use App\Domains\WorkCore\System\Contracts\Records\{ContactAccessContract,CustomerAccessContract,LeadAccessContract};
use App\Domains\WorkCore\System\Contracts\Records\Adapters\{ContactAccessAdapter,CustomerAccessAdapter,LeadAccessAdapter};
use App\Domains\WorkCore\System\ReadModels\{ReadModelDefinition,ReadModelRegistry};
use Illuminate\Support\ServiceProvider;
final class WorkCRMServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CustomerRepositoryContract::class,EloquentCustomerRepository::class);
        $this->app->bind(ContactRepositoryContract::class,EloquentContactRepository::class);
        $this->app->bind(LeadRepositoryContract::class,EloquentLeadRepository::class);
        $this->app->bind(CustomerAccessContract::class,CustomerAccessAdapter::class);
        $this->app->bind(ContactAccessContract::class,ContactAccessAdapter::class);
        $this->app->bind(LeadAccessContract::class,LeadAccessAdapter::class);
        $actions=$this->app->make(BusinessActionRegistry::class);
        foreach ([
            new ActionDefinition('workcore.customer.create',CreateCustomer::class,'medium',true,'workcore.crm',(string)config('workcore.crm.permissions.customers.create')),
            new ActionDefinition('workcore.contact.create',CreateContact::class,'medium',true,'workcore.crm',(string)config('workcore.crm.permissions.contacts.create')),
            new ActionDefinition('workcore.contact.set_primary',SetPrimaryContact::class,'medium',true,'workcore.crm',(string)config('workcore.crm.permissions.contacts.update')),
            new ActionDefinition('workcore.lead.create',CreateLead::class,'medium',true,'workcore.crm',(string)config('workcore.crm.permissions.leads.create')),
            new ActionDefinition('workcore.lead.update',UpdateLead::class,'medium',true,'workcore.crm',(string)config('workcore.crm.permissions.leads.update')),
            new ActionDefinition('workcore.lead.convert',ConvertLeadToCustomer::class,'high',true,'workcore.crm',(string)config('workcore.crm.permissions.leads.convert')),
        ] as $definition) { $actions->register($definition); }
        $read=$this->app->make(ReadModelRegistry::class);
        $read->register(new ReadModelDefinition('workcore.customer.search',SearchCustomers::class,'workcore.crm'));
        $read->register(new ReadModelDefinition('workcore.lead.search',SearchLeads::class,'workcore.crm'));
    }
    public function boot(): void { if(config('workcore.crm.routes_enabled',false)){$this->loadRoutesFrom(__DIR__.'/../routes/api.php');} }
}
