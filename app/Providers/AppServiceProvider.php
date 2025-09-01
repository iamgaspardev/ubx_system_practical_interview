<?php

namespace App\Providers;

use DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Optimize database connections for large queries
        DB::listen(function ($query) {
            if ($query->time > 5000) {
                \Log::warning('Slow query detected', [
                    'sql' => $query->sql,
                    'time' => $query->time,
                    'bindings' => $query->bindings
                ]);
            }
        });

        // Set conservative memory reporting for exports
        if (request()->is('bigdata/export*')) {
            ini_set('memory_limit', config('export.memory_limits.large', '512M'));
            ini_set('max_execution_time', config('export.time_limits.large', 600));
        }
    }
}
