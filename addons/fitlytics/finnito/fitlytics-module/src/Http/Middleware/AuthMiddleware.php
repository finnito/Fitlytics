<?php namespace Finnito\FitlyticsModule\Http\Middleware;

use Closure;
use \Anomaly\UsersModule\User\UserAuthenticator;

class AuthMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!auth()->user()) {
            return redirect("/login");
        }

        return $next($request);
    }
}
