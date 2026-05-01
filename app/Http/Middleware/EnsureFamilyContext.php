<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFamilyContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $familyId = $request->header('X-Family-Id');

        if (! $familyId) {
            return $next($request);
        }

        $activeFamily = $request->user()->families()->find($familyId);

        abort_if(! $activeFamily, 403, 'You do not have access to this family context.');

        $request->attributes->set('active_family', $activeFamily);

        return $next($request);
    }
}
