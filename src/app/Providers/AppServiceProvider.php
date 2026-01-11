<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('layouts.app', function ($view) {
            $status = null;
    
            if (Auth::check()) {
                $attendance = Attendance::where('user_id', Auth::id())
                    ->where('date', now()->toDateString())
                    ->first();
    
                $status = $attendance?->attendanceStatus?->name ?? '勤務外';
            }
    
            $view->with('status', $status);
        });
    }
}
