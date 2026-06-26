<?php

namespace App\Http\Middleware;

use App\Models\Event;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()?->hasPermission($permission)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        $event = $request->route('event');
        if ($event instanceof Event && ! $request->user()->ownsEvent($event)) {
            abort(403, 'You do not have permission to access this event.');
        }

        return $next($request);
    }
}
