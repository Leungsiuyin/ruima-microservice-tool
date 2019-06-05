<?php

namespace Ruima\MicroserviceTool\Middleware;

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
            $res = json_decode($request->input('user'), true);
            if (is_null($res)) {
                throw new \Exception('Unauthorized.');
            }
            $request->auth = $res;
          } catch (\Throwable $th) {
            //throw $th;
            return response('Unauthorized.', 401);
        }

        return $next($request);
    }
}