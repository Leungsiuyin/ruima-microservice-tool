<?php

namespace Ruima\MicroserviceTool\Middleware;

use Closure;

class Permission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $params)
    {
        if (!isset($request->auth['permissons'])) {
            FackAuth::handle($request, $next);
        }
        $require_permission = explode(" ", $params);

        $user_permission = $request->auth['permissons'];

        $have_permission = false;

        foreach ($require_permission as $key => $value) {
            # code...
            if ($value !== "") {
                if (array_search($value, $user_permission) !== false) {
                    $have_permission = true;
                }
            }
        }

        if (!$have_permission) {
            return response('Forbidden' , 403);
        }

        return $next($request);
    }
}