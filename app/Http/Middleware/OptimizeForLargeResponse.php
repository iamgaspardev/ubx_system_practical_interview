<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OptimizeForLargeResponse
{
    public function handle(Request $request, Closure $next)
    {
        // Disable output buffering for streaming responses
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers for large file downloads
        if ($request->is('bigdata/export*')) {
            ignore_user_abort(true);
            set_time_limit(0);
            ini_set('zlib.output_compression', 'Off');
        }

        $response = $next($request);

        return $response;
    }
}