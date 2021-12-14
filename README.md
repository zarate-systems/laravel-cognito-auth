# Laravel AWS Cognito Auth

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zarate-systems/laravel-cognito-auth.svg?style=flat-square)](https://packagist.org/packages/zarate-systems/laravel-cognito-auth)
[![Total Downloads](https://img.shields.io/packagist/dt/zarate-systems/laravel-cognito-auth.svg?style=flat-square)](https://packagist.org/packages/zarate-systems/laravel-cognito-auth)

This package is forked from [ArranJacques/laravel-aws-cognito-auth.](https://github.com/ArranJacques/laravel-aws-cognito-auth) but updated to Laravel 8.

A simple authentication package for Laravel 8 for authenticating users in Amazon Cognito User Pools.

This is package works with Laravel's native authentication system and allows the authentication of users that are already registered in Amazon Cognito User Pools. It does not provide functionality for user management, i.e., registering user's into User Pools, password resets, etc.

## Installation

This package makes use of the aws-sdk-php-laravel package. As well as setting up and configuring this package you'll also need to configure aws-sdk-php-laravel for the authentication to work. Instructions on how to do this are below. If you've already installed, set up and configured aws-sdk-php-laravel you can skip the parts where it's mentioned below.

You can install the package via composer:

```bash
composer require zarate-systems/laravel-cognito-auth
```

### Configuration

Open `app/Http/Kernel.php` and replace the default `\Illuminate\Session\Middleware\AuthenticateSession::class` middleware with `\ZarateSystems\LaravelCognitoAuth\AuthenticateSession::class`

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
AWS_DEFAULT_REGION=YOUR_DEFAULT_REGION
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
### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email zaraterick@outlook.com instead of using the issue tracker.

## Credits

-   [Erick Zarate](https://github.com/zarate-systems)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
