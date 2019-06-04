<?php

namespace Ruima\MicroserviceTool\Provider;

// use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Ruima\MicroserviceTool\Master;
use Ruima\MicroserviceTool\Console\Commands\HeartBeatCheck;

class MasterProvider extends ServiceProvider
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
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {

        Master::routes($this->app->router);# 注册Slaver相关路由

        #添加heart beat check
        $schedule = $this->app->make(Schedule::class);
        // $schedule->command('some:command')->everyMinute();
        $schedule->call(function () {
            HeartBeatCheck::handle();
        })
        ->everyFifteenMinutes()
        // ->everyMinute()
        ->runInBackground()
        ->name('heart_beat_check')
        ->withoutOverlapping();
    }
}
