<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Session;
use Illuminate\Support\Facades\Redirect;

class SingleSession
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
        if(Auth::check()) {
            if(Auth::user()->session_id != Session::getId()) {
                Auth::logout();

                return Redirect::to('/');
            }
        }

        return $next($request);
    }
}
