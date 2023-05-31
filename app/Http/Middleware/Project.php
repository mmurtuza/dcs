<?php

namespace App\Http\Middleware;

use Closure;

class Project
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
        if(!session()->has('project'))
        {
            return redirect('/dashboard')->with('error', 'Select the project first');
        }
        return $next($request);
    }
}
