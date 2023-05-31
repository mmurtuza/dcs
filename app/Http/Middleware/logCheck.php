<?php

namespace App\Http\Middleware;

use Closure;

class logCheck
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
        // dd(session()->has('roll'));
        if (!session()->has('roll')) {
            // dd(session()->all());
            return redirect("/loginpage");
        }
        return $next($request);
    }
}
