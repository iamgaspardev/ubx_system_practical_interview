<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\BigDataUploadService;

class BigDataServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(BigDataUploadService::class, function ($app) {
            return new BigDataUploadService();
        });
    }
}