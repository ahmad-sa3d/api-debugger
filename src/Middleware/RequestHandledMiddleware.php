<?php

namespace Lanin\Laravel\ApiDebugger\Middleware;

use Closure;
use Lanin\Laravel\ApiDebugger\Debugger;

class RequestHandledMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        /** @var Debugger $debugger */
        app(Debugger::class)->requestHandled($request, $response);

        return $response;
    }
}
