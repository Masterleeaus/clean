<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Models;

use App\Domains\WorkCore\System\Support\HasPublicId;
use App\Domains\WorkCore\System\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

abstract class TenantModel extends Model
{
    use BelongsToCompany;
    use HasPublicId;

    protected $guarded = ['id'];

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
