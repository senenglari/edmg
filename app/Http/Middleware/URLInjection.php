<?php

namespace App\Http\Middleware;

use Closure;
use Request;
use URL;
use Auth;
use App\Model\UserManagement\MenuModel;

class URLInjection
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
        $qMenu          = new MenuModel();
        $path           = "/" . Request::path();
        
        $data           = $qMenu->getAccessStatus($path, Auth::user()->id);

        if(count($data) == 0) {
            return redirect()->route('url.injection');
        }

        return $next($request);
    }
}
