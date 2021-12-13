<?php

namespace ZarateSystems\LaravelCognitoAuth;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ZarateSystems\LaravelCognitoAuth\Skeleton\SkeletonClass
 */
class LaravelCognitoAuthFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-cognito-auth';
    }
}
