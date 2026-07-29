<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanPay\Enums\CollectionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class CollectionSession extends Model
{
    protected $hidden = ['public_token_hash','public_token_ciphertext'];
    use BelongsToCompany, HasUlids;
    protected $table = 'titanpay_collection_sessions';
    protected $fillable = ['company_id','invoice_id','receivable_type','receivable_id','gateway_connection_id','reference','public_token_hash','public_token_ciphertext','public_token_hint','amount_minor','currency_code','allowed_methods_json','selected_method','payer_context_json','return_url','status','gateway_reference','gateway_payload_json','expires_at','opened_at','completed_at','failed_at','failure_reason','created_by_user_id','created_by_agent_id','conversation_id','correlation_id'];
    protected function casts(): array { return ['amount_minor'=>'integer','allowed_methods_json'=>'array','payer_context_json'=>'array','gateway_payload_json'=>'array','status'=>CollectionStatus::class,'expires_at'=>'datetime','opened_at'=>'datetime','completed_at'=>'datetime','failed_at'=>'datetime']; }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function gatewayConnection(): BelongsTo { return $this->belongsTo(GatewayConnection::class); }
    public function transactions(): HasMany { return $this->hasMany(Transaction::class); }
    public function claims(): HasMany { return $this->hasMany(PaymentClaim::class); }
    public function qrCode(): HasOne { return $this->hasOne(QrCode::class); }
    public function isExpired(): bool { return $this->expires_at?->isPast() ?? false; }
}
