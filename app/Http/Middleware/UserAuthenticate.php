<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserAuthenticate
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
        if(!request()->session()->has('userID')){
            if($request->ajax()){
                return response('Unauthorized.',401);                
            }else{
                return redirect()->guest('/login');
            }
        }

        return $next($request);
    }
}
