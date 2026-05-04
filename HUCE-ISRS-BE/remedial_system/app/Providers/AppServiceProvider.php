<?php

namespace App\Providers;

use App\Models\TutoringClass;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::model('tutoringClass', TutoringClass::class);
        
        // Route model binding: {user} → User
        Route::model('user', \App\Models\User::class);
    }
}
