<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domains\Company\Support\CompanyPermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireCompanyPermission
{
    public function __construct(private readonly CompanyPermission $permissions) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        abort_unless($request->user() && $this->permissions->allows($request->user(), $permission), 403);
        return $next($request);
    }
}
