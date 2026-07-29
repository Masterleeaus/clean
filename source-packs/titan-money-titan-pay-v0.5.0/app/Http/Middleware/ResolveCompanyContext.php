<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domains\Company\Support\ActorContext;
use App\Domains\Company\Support\CurrentCompany;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResolveCompanyContext
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly ActorContext $actorContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $this->actorContext->setHuman($request);

        if ($user) {
            $headerCompany = $request->header('X-Company-Id');
            $sessionCompany = $request->hasSession()
                ? $request->session()->get('active_company_id')
                : null;
            $requested = $headerCompany ?: $sessionCompany;
            $company = $this->currentCompany->resolveFor($user, $requested, $headerCompany !== null);

            if ($request->hasSession()) {
                $request->session()->put('active_company_id', $company->id);
            }
        }

        return $next($request);
    }
}
