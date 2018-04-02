<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TestService;

class TestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('testsingle',function(){
            return new TestService();
        });
        $this->app->bind('testbind',function(){
            return new TestService();
        });

        //使用bind绑定实例到接口以便依赖注入
        $this->app->bind('App\Contracts\TestContract',function(){
            return new TestService();
        });
    }
}
