# Rencanakan ID System Backend

This is Rencanakan ID Backend, this application is built on top of [Laravel](https://laravel.com/) Framework.

## Setup

```
composer install
cp .env.example .env
# Change .env content with your preference
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## API

```
+--------+----------+-----------------------------------------+------------------------+---------------------------------------------------------------+------------------------------------------+
| Domain | Method | URI | Name | Action | Middleware |
+--------+----------+-----------------------------------------+------------------------+---------------------------------------------------------------+------------------------------------------+
| | GET|HEAD | / | | Closure | web |
| | POST | api/auth/login | | App\Http\Controllers\Auth\LoginController@login | api |
| | POST | api/auth/logout | | App\Http\Controllers\Auth\LoginController@logout | api |
| | | | | | App\Http\Middleware\Authenticate:sanctum |
| | POST | api/auth/register | | App\Http\Controllers\Auth\RegisterController@register | api |
| | POST | api/auth/verify | | App\Http\Controllers\Auth\LoginController@verify | api |
| | | | | | App\Http\Middleware\Authenticate:sanctum |
| | POST | api/payment/demo-add-token | | App\Http\Controllers\Payment\PaymentController@addToken | api |
| | | | | | App\Http\Middleware\Authenticate:sanctum |
| | POST | api/payment/fetch-snap-token | | App\Http\Controllers\Payment\PaymentController@fetchSnapToken | api |
| | | | | | App\Http\Middleware\Authenticate:sanctum |
| | GET|HEAD | api/user | | Closure | api |
| | | | | | App\Http\Middleware\Authenticate:sanctum |
| | GET|HEAD | api/user/{user} | | App\Http\Controllers\User\UserController@show | api |
| | | | | | App\Http\Middleware\Authenticate:sanctum |
| | POST | api/user/{user} | | App\Http\Controllers\User\UserController@update | api |
| | | | | | App\Http\Middleware\Authenticate:sanctum |
| | GET|HEAD | auth/email-verification/confirm/{token} | register.confirm_email | App\Http\Controllers\Auth\RegisterController@confirmEmail | web |
| | GET|HEAD | sanctum/csrf-cookie | | Laravel\Sanctum\Http\Controllers\CsrfCookieController@show | web |
+--------+----------+-----------------------------------------+------------------------+---------------------------------------------------------------+------------------------------------------+
```
