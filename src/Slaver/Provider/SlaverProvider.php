<?php

namespace Ruima\MicroserviceTool\Provider;

// use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Ruima\MicroserviceTool\Slaver;
use Ruima\MicroserviceTool\Console\Commands\HeartBeat;

class SlaverProvider extends ServiceProvider
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

        Slaver::routes($this->app->router);# 注册Slaver相关路由

        #添加heart beat
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            // $schedule->command('some:command')->everyMinute();
            $schedule->call(function () {
                HeartBeat::handle();
            })
            ->everyFifteenMinutes()
            ->runInBackground()
            ->name('heart_beat')
            ->withoutOverlapping();
        });
    }
}
