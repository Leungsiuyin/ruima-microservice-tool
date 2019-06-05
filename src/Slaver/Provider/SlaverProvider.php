<?php

namespace Ruima\MicroserviceTool\Provider;

// use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Ruima\MicroserviceTool\Slaver;
use Ruima\MicroserviceTool\Console\Commands\HeartBeat;

class SlaverProvider extends ServiceProvider
{

    protected $commands = [
        HeartBeat::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->commands($this->commands);
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
        $schedule = $this->app->make(Schedule::class);
        $schedule->command(HeartBeat::class)
            ->everyFiveMinutes()
            // ->everyMinute()
            ->runInBackground()
            ->name('heart_beat')
            ->withoutOverlapping();
    }
}
