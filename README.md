# Laravel AWS Cognito Auth

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zarate-systems/laravel-cognito-auth.svg?style=flat-square)](https://packagist.org/packages/zarate-systems/laravel-cognito-auth)
[![Total Downloads](https://img.shields.io/packagist/dt/zarate-systems/laravel-cognito-auth.svg?style=flat-square)](https://packagist.org/packages/zarate-systems/laravel-cognito-auth)
[![StyleCI](https://github.styleci.io/repos/438319368/shield?branch=master)](https://github.styleci.io/repos/438319368?branch=master)

This package is forked from [ArranJacques/laravel-aws-cognito-auth](https://github.com/ArranJacques/laravel-aws-cognito-auth) but updated to Laravel 8.

A simple authentication package for Laravel 8 for authenticating users in Amazon Cognito User Pools.

This is package works with Laravel's native authentication system and allows the authentication of users that are already registered in Amazon Cognito User Pools. It does not provide functionality for user management, i.e., registering user's into User Pools, password resets, etc.

## Installation

This package makes use of the aws-sdk-php-laravel package. As well as setting up and configuring this package you'll also need to configure aws-sdk-php-laravel for the authentication to work. Instructions on how to do this are below. If you've already installed, set up and configured aws-sdk-php-laravel you can skip the parts where it's mentioned below.

You can install the package via composer:

```bash
composer require zarate-systems/laravel-cognito-auth
```

### Configuration

Open the `app/Http/Kernel.php` file and replace the default `\Illuminate\Session\Middleware\AuthenticateSession::class` middleware with `\ZarateSystems\LaravelCognitoAuth\AuthenticateSession::class`

```php
protected $middlewareGroups = [
    'web' => [
        ...
        \ZarateSystems\LaravelCognitoAuth\AuthenticateSession::class,
        ...
    ],
];
```

Publish the config file as well as the aws-sdk-php-laravel config file to your config directory by running:

```bash
php artisan vendor:publish --provider="ZarateSystems\LaravelCognitoAuth\LaravelCognitoAuthServiceProvider"
```

```bash
php artisan vendor:publish --provider="Aws\Laravel\AwsServiceProvider"
```

Open the `config/auth.php` file and set your default guard's driver to `aws-cognito`. Out of the box the default guard is `web` so your `config/auth.php` would look something like:

```php
'defaults' => [
    'guard' => 'web',
    'passwords' => 'users',
],
```

Change the driver.

```php
'guards' => [
    'web' => [
        'driver' => 'aws-cognito',
        'provider' => 'users',
    ],
],
```

Add the next envioremnt variables to `.env` file.

```dotenv
AWS_COGNITO_IDENTITY_POOL_ID=YOUR_POOL_ID
AWS_COGNITO_IDENTITY_APP_CLIENT_ID=YOUR_CLIENT_ID
```

- Note
  > When creating an App for your User Pool the default Refresh Token Expiration time is 30 days. If you've set a different expiration time for your App then make sure you update the `refresh-token-expiration` value in the `config/aws-cognito-auth.php` file.

```php
'apps' => [
    'default' => [
        'client-id' => env('AWS_COGNITO_IDENTITY_APP_CLIENT_ID', ''),
        'refresh-token-expiration' => <num-of-days>,
    ],
],
```

Open the `config/aws.php` file and set the `region` value to whatever region your User Pool is in. The default `config/aws.php` file that is created when using the `php artisan vendor:publish --provider="Aws\Laravel\AwsServiceProvider"` command doesn't include the IAM credential properties so you'll need to add them manually. 

Add the following to the `.env` file.

```dotenv
AWS_ACCESS_KEY_ID=YOUR_ACCESS_KEY_ID
AWS_SECRET_ACCESS_KEY=YOUR_SECRET_ACCESS_KEY
AWS_REGION=YOUR_DEFAULT_REGION
```

Where `AWS_ACCESS_KEY_ID` is an IAM user Access Key Id and `AWS_SECRET_ACCESS_KEY` is the corresponding Secret key.

## Users Table

Cognito is not treated as a "database of users", and is only used to authorize the users. A request to Cognito is not made unless the user already exists in the app's database. This means you'll still need a users table populated with the users that you want to authenticate. At a minimum this table will need an `id`, `email` and `remember_token` field.

In Cognito every user has a `username`. When authenticating with Cognito this package will need one of the user's attributes to match the user's Congnito username. By default it uses the user's `email` attribute.

If you want to use a different attribute to store the user's Cognito username then you can do so by first adding a new field to your `users` table, for example cognito_username, and then setting the `username-attribute` in the `config/aws-cognito-auth.php` file to be the name of that field.

## Usage

Once installed and configured the authentication works the same as it doesn natively in Laravel. See Laravel's documentation for full details.

### Authenticating

#### Authenticate:

```php
Auth::attempt([
    'email' => 'xxxxx@xxxxx.xx',
    'password' => 'xxxxxxxxxx',
]);
```

#### Authenticate and remember:

```php
Auth::attempt([
    'email' => 'xxxxx@xxxxx.xx',
    'password' => 'xxxxxxxxxx',
], true);
```

#### Get the authenticated user:

```php
Auth::user();
```

#### Logout:

```php
Auth::logout();
```

As well as the default functionality some extra methods are made available for accessing the user's Cognito access token, id token, etc:

```php
Auth::getCognitoAccessToken();
```

```php
Auth::getCognitoIdToken();
```

```php
Auth::getCognitoRefreshToken();
```

```php
Auth::getCognitoTokensExpiryTime();
```

```php
Auth::getCognitoRefreshTokenExpiryTime();
```
### Handling Failed Authentication

AWS Cognito may fail to authenticate for a number of reasons, from simply entering the wrong credentials, or because additional checks or actions are required before the user can be successfully authenticated.

So that you can deal with failed attempts appropriately several options are available to you within the package that dictate how failed attempts should be handled.

#### Methods

You can specify how failed attempts should be handled by passing an additional `$errorHandler` argument when calling the `Auth::attempt()` and `Auth::validate()` methods.

```php
Auth::attempt(array $credentials, [bool $remember], [$errorHandler]);

Auth::validate(array $credentials, [$errorHandler]);
```

##### No Error Handling
If an `$errorHandler` isn't passed then all failed authentication attempts will be handled and suppressed internally, and both the `Auth::attempt()` and `Auth::validate()` methods will simply return true or false as to whether the authentication attempt was successful.

##### Throw Exception

To have the `Auth::attempt()` and `Auth::validate()` methods throw an exception pass `AWS_COGNITO_AUTH_THROW_EXCEPTION` as the `$errorHandler` argument.

```php
Auth::attempt([
    'email' => 'xxxxx@xxxxx.xx',
    'password' => 'xxxxxxxxxx',
], false, AWS_COGNITO_AUTH_THROW_EXCEPTION);

Auth::validate([
    'email' => 'xxxxx@xxxxx.xx',
    'password' => 'xxxxxxxxxx',
], AWS_COGNITO_AUTH_THROW_EXCEPTION);
```

If the authentication fails then a `\ZarateSystems\LaravelCognitoAuth\AuthAttemptException` exception will be thrown, which can be used to access the underlying error by calling the exception's `getResponse()` method. [About AuthAttemptException](#about-authattemptexception).

```php
try {
    Auth::attempt([
        'email' => 'xxxxx@xxxxx.xx',
        'password' => 'xxxxxxxxxx',
    ], false, AWS_COGNITO_AUTH_THROW_EXCEPTION);
} catch (\ZarateSystems\LaravelCognitoAuth\AuthAttemptException $exception) {
    $response = $exception->getResponse();
    // Handle error...
}
```

##### Return Attempt Instance

To have the `Auth::attempt()` and `Auth::validate()` methods return an attempt object pass `AWS_COGNITO_AUTH_RETURN_ATTEMPT` as the `$errorHandler` argument.

```php
Auth::attempt([
    'email' => 'xxxxx@xxxxx.xx',
    'password' => 'xxxxxxxxxx',
], false, AWS_COGNITO_AUTH_RETURN_ATTEMPT);

Auth::validate([
    'email' => 'xxxxx@xxxxx.xx',
    'password' => 'xxxxxxxxxx',
], AWS_COGNITO_AUTH_RETURN_ATTEMPT);
```

When using `AWS_COGNITO_AUTH_RETURN_ATTEMPT` both methods will return an instance of `\ZarateSystems\LaravelCognitoAuth\AuthAttempt`, which can be used to check if the authentication attempt was successful or not.

```php
$attempt = Auth::attempt([
    'email' => 'xxxxx@xxxxx.xx',
    'password' => 'xxxxxxxxxx',
], false, AWS_COGNITO_AUTH_RETURN_ATTEMPT);

if ($attempt->successful()) {
    // Do something...
} else {
    $response = $attempt->getResponse();
    // Handle error...
}
```

For unsuccessful authentication attempts the attempt instance's `getResponse()` method can be used to access the underlying error. This method will return an array of data that will contain different values depending on the reason for the failed attempt.

In events where the AWS Cognito API has thrown an exception, such as when invalid credentials are used, the array that is returned will contain the original exception.

```php
[
    'exception' => CognitoIdentityProviderException {...},
]
```

In events where the AWS Cognito API has failed to authenticate for some other reason, for example because a challenge must be passed, then the array that is returned will contain the details of the error.

```php
[
    'ChallengeName' => 'NEW_PASSWORD_REQUIRED',
    'Session' => '...',
    'ChallengeParameters' => [...],
]
```

##### Using a Closure

To handle failed authentication attempts with a closure pass one as the `Auth::attempt()` and `Auth::validate()` methods' `$errorHandler` argument.

```php
Auth::attempt([
    'email' => 'xxxxx@xxxxx.xx',
    'password' => 'xxxxxxxxxx',
], false, function (\ZarateSystems\LaravelCognitoAuth\AuthAttemptException $exception) {
    $response = $exception->getResponse();
    // Handle error...
});

Auth::validate([
    'email' => 'xxxxx@xxxxx.xx',
    'password' => 'xxxxxxxxxx',
], function (\ZarateSystems\LaravelCognitoAuth\AuthAttemptException $exception) {
    $response = $exception->getResponse();
    // Handle error...
};
```

If the authentication fails then the closure will be run and will be passed an `\ZarateSystems\LaravelCognitoAuth\AuthAttemptException` instance, which can be used to access the underlying error by calling the exception's `getResponse()` method. [About AuthAttemptException](#about-authattemptexception).

##### Using a Custom Class

To handle failed authentication attempts with a custom class pass the classes name as the `Auth::attempt()` and `Auth::validate()` methods' `$errorHandler` argument.

```php
Auth::attempt([
    'email' => 'xxxxx@xxxxx.xx',
    'password' => 'xxxxxxxxxx',
], false, \App\MyCustomErrorHandler::class);

Auth::validate([
    'email' => 'xxxxx@xxxxx.xx',
    'password' => 'xxxxxxxxxx',
], \App\MyCustomErrorHandler::class);
```

The error handler class should have a `handle()` method, which will be called when an authentication attempt fails. The `handle()` method will be passed an `\ZarateSystems\LaravelCognitoAuth\AuthAttemptException` instance, which can be used to access the underlying error by calling the exception's `getResponse()` method. [About AuthAttemptException](#about-authattemptexception).

```php
<?php

namespace App;

use ZarateSystems\LaravelCognitoAuth\AuthAttemptException;

class MyCustomErrorHandler
{
    public function handle(AuthAttemptException $exception)
    {
        $response = $exception->getResponse();
        // Handle error...
    }
}

```

#### Default Error Handler

As well defining the error handler in line, you can also define a default error handler in the `config/aws-cognito-auth.php` file. The same error handling methods are available as detailed above. When using `AWS_COGNITO_AUTH_THROW_EXCEPTION` or `AWS_COGNITO_AUTH_RETURN_ATTEMPT` set the value as a string, do not use the constant.

**Throw Exception:**

```php
'errors' => [
    'handler' => 'AWS_COGNITO_AUTH_THROW_EXCEPTION',
],
```

**Return Attempt:**

```php
'errors' => [
    'handler' => 'AWS_COGNITO_AUTH_RETURN_ATTEMPT',
],
```

**Use a Closure:**

```php
'errors' => [
    'handler' => function (\ZarateSystems\LaravelCognitoAuth\AuthAttemptException $exception) {
        $exception->getResponse();
        // Do something...
    },
],
```

**Use a Custom Class:**

```php
'errors' => [
    'handler' => \App\MyCustomErrorHandler::class,
],
```

#### About AuthAttemptException

An `\ZarateSystems\LaravelCognitoAuth\AuthAttemptException` exception will be thrown when using the `AWS_COGNITO_AUTH_THROW_EXCEPTION` error handler, or will be passed as an argument to a closure when using the `Clousre` method of error handling.

The exception's `getResponse()` method will return an array of data that will contain different values depending on the reason for the failed attempt.

In events where the AWS Cognito API has thrown an exception, such as when invalid credentials are used, the array that is returned will contain the original exception.

```php
[
    'exception' => CognitoIdentityProviderException {...},
]
```

In events where the AWS Cognito API has failed to authenticate for some other reason, for example because a challenge must be passed, the array that is returned will contain the details of the error.

```php
[
    'ChallengeName' => 'NEW_PASSWORD_REQUIRED',
    'Session' => '...',
    'ChallengeParameters' => [...],
]
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email zaraterick@outlook.com instead of using the issue tracker.

## Credits

-   [Arran Jacques](https://github.com/ArranJacques)
-   [Erick Zarate](https://github.com/ErickZH)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
