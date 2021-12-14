<?php

namespace ZarateSystems\LaravelCognitoAuth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class LaravelCognitoAuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/aws-cognito-auth.php' => config_path('aws-cognito-auth.php'),
            ], 'config');
        }

        $this->registerGuard();

        $this->defineConstants();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/aws-cognito-auth.php', 'aws-cognito-auth');
    }

    /**
     * Register the AWS Cognito guard.
     */
    protected function registerGuard()
    {
        Auth::extend('aws-cognito', function ($app, $name, array $config) {
            $client = $app->make('aws')->createCognitoIdentityProvider();
            $provider = Auth::createUserProvider($config['provider']);
            $guard = new AwsCognitoIdentityGuard(
                $name,
                $client,
                $provider,
                $app['session.store'],
                $app['request'],
                $app['config']['aws-cognito-auth']
            );

            $guard->setCookieJar($this->app['cookie']);

            $guard->setDispatcher($this->app['events']);

            $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));

            return $guard;
        });
    }

    /**
     * Define constants related to the package.
     */
    public function defineConstants()
    {
        if (!defined('AWS_COGNITO_AUTH_THROW_EXCEPTION')) {
            define('AWS_COGNITO_AUTH_THROW_EXCEPTION', 'throw-exception');
        }

        if (!defined('AWS_COGNITO_AUTH_RETURN_ATTEMPT')) {
            define('AWS_COGNITO_AUTH_RETURN_ATTEMPT', 'return-attempt');
        }
    }
}
