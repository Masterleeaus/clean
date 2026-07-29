<?php

declare(strict_types=1);
namespace App\Domains\WorkCore\System\Modules\CRM\Services;
use App\Domains\WorkCore\System\Modules\CRM\Support\CrmNormalizer;
use App\Domains\WorkCore\System\Validation\CompanyScopedReferenceValidator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use RuntimeException;
final class LeadLifecycleService
{
    public function __construct(private ConnectionInterface $db, private CompanyScopedReferenceValidator $references) {}
    public function update(int $companyId,string $publicId,int $actorId,array $data): array
    {
        $lead=$this->lead($companyId,$publicId);
        if (isset($data['owner_id']) && $data['owner_id']!==null) { $this->references->requireActiveMember($companyId,(int)$data['owner_id'],'owner_id'); }
        if (isset($data['source_public_id'])) { $data['source_id']=$this->lookup($companyId,'tz_lead_sources',(string)$data['source_public_id']); unset($data['source_public_id']); }
        if (isset($data['status_public_id'])) { $data['status_id']=$this->lookup($companyId,'tz_lead_statuses',(string)$data['status_public_id']); unset($data['status_public_id']); }
        $allowed=['name','email','phone','company_name','notes','owner_id','source_id','status_id','priority','follow_up_at'];
        $updates=array_intersect_key($data,array_flip($allowed));
        if (array_key_exists('email',$updates)) { $updates['normalized_email']=CrmNormalizer::email($updates['email']); $updates['email']=$updates['normalized_email']; }
        if (array_key_exists('phone',$updates)) { $updates['normalized_phone']=CrmNormalizer::phone($updates['phone']); $updates['phone']=$updates['normalized_phone']; }
        if ($updates===[]) { return $this->snapshot($companyId,$publicId); }
        $updates['updated_by_user_id']=$actorId; $updates['updated_at']=now();
        $this->db->table('tz_leads')->where('id',$lead->id)->update($updates);
        $this->activity($companyId,$lead->id,$actorId,'updated',$updates);
        return $this->snapshot($companyId,$publicId);
    }
    public function convert(int $companyId,string $publicId,int $actorId): array
    {
        $lead=$this->lead($companyId,$publicId);
        if ($lead->converted_customer_id!==null) { return $this->snapshot($companyId,$publicId); }
        return $this->db->transaction(function() use($lead,$companyId,$publicId,$actorId): array {
            $customerPublicId=(string)Str::ulid();
            $customerId=$this->db->table('tz_customers')->insertGetId(['public_id'=>$customerPublicId,'company_id'=>$companyId,'name'=>$lead->name,'legal_name'=>$lead->company_name,'email'=>$lead->email,'normalized_email'=>$lead->normalized_email,'phone'=>$lead->phone,'normalized_phone'=>$lead->normalized_phone,'notes'=>$lead->notes,'status'=>'active','created_by_user_id'=>$actorId,'updated_by_user_id'=>$actorId,'created_at'=>now(),'updated_at'=>now()]);
            $won=$this->db->table('tz_lead_statuses')->where('company_id',$companyId)->where('key','won')->value('id');
            $this->db->table('tz_leads')->where('id',$lead->id)->update(['converted_customer_id'=>$customerId,'converted_at'=>now(),'status_id'=>$won,'updated_by_user_id'=>$actorId,'updated_at'=>now()]);
            $this->activity($companyId,$lead->id,$actorId,'converted',['customer_public_id'=>$customerPublicId]);
            $result=$this->snapshot($companyId,$publicId); $result['customer_public_id']=$customerPublicId; return $result;
        });
    }
    public function options(int $companyId): array
    {
        return ['sources'=>$this->db->table('tz_lead_sources')->where('company_id',$companyId)->where('is_active',true)->whereNull('deleted_at')->orderBy('sort_order')->get(['public_id','key','name'])->map(fn($r)=>(array)$r)->all(),'statuses'=>$this->db->table('tz_lead_statuses')->where('company_id',$companyId)->whereNull('deleted_at')->orderBy('sort_order')->get(['public_id','key','name','is_closed','is_won'])->map(fn($r)=>(array)$r)->all()];
    }
    private function lead(int $companyId,string $publicId): object { $lead=$this->db->table('tz_leads')->where('company_id',$companyId)->where('public_id',$publicId)->whereNull('deleted_at')->first(); if(!$lead){throw new RuntimeException('Lead was not found in the active company.');} return $lead; }
    private function lookup(int $companyId,string $table,string $publicId): int { $id=$this->db->table($table)->where('company_id',$companyId)->where('public_id',$publicId)->whereNull('deleted_at')->value('id'); if(!$id){throw new RuntimeException('CRM reference was not found in the active company.');} return (int)$id; }
    private function snapshot(int $companyId,string $publicId): array { return (array)$this->db->table('tz_leads as l')->leftJoin('tz_lead_sources as s','s.id','=','l.source_id')->leftJoin('tz_lead_statuses as st','st.id','=','l.status_id')->where('l.company_id',$companyId)->where('l.public_id',$publicId)->first(['l.public_id','l.name','l.email','l.phone','l.company_name','l.notes','l.owner_user_id','l.priority','l.follow_up_at','l.converted_at','s.public_id as source_public_id','s.name as source_name','st.public_id as status_public_id','st.name as status_name']); }
    private function activity(int $companyId,int $leadId,int $actorId,string $type,array $payload): void { $this->db->table('tz_lead_activities')->insert(['public_id'=>(string)Str::ulid(),'company_id'=>$companyId,'lead_id'=>$leadId,'type'=>$type,'payload'=>json_encode($payload,JSON_THROW_ON_ERROR),'actor_user_id'=>$actorId,'created_at'=>now(),'updated_at'=>now()]); }
}
