<?php

namespace MicroserviceTool;

use Closure;

class FackAuth
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
        try {
            //code...
            if (is_null(json_decode($request->input('user'), true))) {
                throw new \Exception('Unauthorized.');
            }
          } catch (\Throwable $th) {
            //throw $th;
            return response('Unauthorized.', 401);
        }

        return $next($request);
    }
}