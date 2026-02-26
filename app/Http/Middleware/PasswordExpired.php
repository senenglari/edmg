<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use App\Model\Sys\SysModel;

class PasswordExpired
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
        $user                   = $request->user();
        $config                 = new SysModel;
        # -----------------------
        $dataConfig             = $config->getConfig();
        $password_changed_at    = new Carbon($user->password_changed_at);
        # -----------------------
        if(empty($user->password_changed_at)) {
            return redirect()->route('password.expired');
        } else {
            if (Carbon::now()->diffInDays($password_changed_at) >= $dataConfig->password_expired) {
                return redirect()->route('password.expired');
            }
        }

        return $next($request);
    }
}
