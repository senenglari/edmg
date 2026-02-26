<?php

namespace App\Http\Middleware;

use Closure;
use App\Model\Sys\SysModel;

class MaintenanceMode
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
        $qSys       = new SysModel();

        $dataSys    = $qSys->getConfig();

        if ($dataSys->maintenance_sys_mode == 1) {
            return redirect()->route('maintenance.mode');
        }

        return $next($request);
    }
}
