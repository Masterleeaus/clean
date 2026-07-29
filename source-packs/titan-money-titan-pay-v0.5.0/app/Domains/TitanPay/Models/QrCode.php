<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class QrCode extends Model
{
    use HasUlids;
    protected $table='titanpay_qr_codes';
    protected $fillable=['collection_session_id','payload','format','expires_at','metadata_json'];
    protected function casts(): array { return ['expires_at'=>'datetime','metadata_json'=>'array']; }
}
