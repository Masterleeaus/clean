<?php

declare(strict_types=1);

namespace App\Extensions\AiSiteBuilderBridge\Models;

use Illuminate\Database\Eloquent\Model;

final class SiteBuilderProject extends Model
{
    protected $table = 'ai_site_builder_projects';

    protected $fillable = [
        'company_id', 'user_id', 'external_project_id', 'webby_project_id',
        'name', 'status', 'launch_url', 'preview_url', 'metadata', 'last_event_id',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'user_id' => 'integer',
        'webby_project_id' => 'integer',
        'metadata' => 'array',
    ];
}
