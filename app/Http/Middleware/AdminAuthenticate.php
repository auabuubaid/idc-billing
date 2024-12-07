<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(!request()->session()->has('adminID')){
            if($request->ajax()){
                return response('Unauthorized.',401);                
            }else{
                return redirect()->guest('/admin/login');
            }
        }

        return $next($request);
    }
}
