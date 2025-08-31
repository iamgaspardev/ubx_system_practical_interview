<?php

namespace App\Http\Middleware;

use Closure;

class SetTimeLimit
{
    public function handle($request, Closure $next, $timeLimit = 300)
    {
        set_time_limit($timeLimit);
        ini_set('memory_limit', '512M');

        return $next($request);
    }
}