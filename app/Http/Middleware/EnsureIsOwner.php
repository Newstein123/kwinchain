<?php

namespace App\Http\Middleware;

use App\Models\Owner;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsOwner
{
    /**
     * Allow the request through only if the authenticated model is an Owner.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() instanceof Owner) {
            return response()->json(['message' => 'Forbidden. Owner access required.'], 403);
        }

        return $next($request);
    }
}
