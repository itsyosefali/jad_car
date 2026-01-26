<?php

namespace App\Providers;

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
    public function boot(): void
    {
        // تعيين locale للأرقام لاستخدام الأرقام الإنجليزية بدلاً من العربية
        setlocale(LC_NUMERIC, 'en_US.UTF-8', 'en_US', 'en');
    }
}
