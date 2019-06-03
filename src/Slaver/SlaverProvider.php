<?php

namespace Ruima\MicroserviceTool\Provider;

// use App\Models\User;
use Illuminate\Support\ServiceProvider;

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
    }
}
