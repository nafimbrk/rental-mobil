<?php

namespace App\Providers;

use App\Events\PembayaranSewaSelesai;
use App\Listeners\KirimNotifikasiWhatsApp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    protected $listen = [
        PembayaranSewaSelesai::class => [
            KirimNotifikasiWhatsApp::class,
        ],
    ];

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
         DB::listen(function($query) {
            Log::info(
                $query->sql,
                [
                    'bindings' => $query->bindings,
                    'time' => $query->time
                ]
            );
        });
    }
}
