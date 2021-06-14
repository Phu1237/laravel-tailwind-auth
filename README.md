# Laravel Tailwind Authentication

![Banner](https://banners.beyondco.de/Laravel%20Tailwind%20Authentication.png?theme=light&packageManager=composer+require&packageName=phu1237%2Flaravel-tailwind-auth&pattern=circuitBoard&style=style_1&description=Simple+Laravel+Authentication+using+Tailwindcss+%26+Blade&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

## Introduction

Laravel Tailwind Authentication provides a minimal and simple starting point for building a Laravel application with authentication. Styled with Tailwind, this package publishes authentication controllers and views to your application that can be easily customized based on your own application's needs.

This package is based on [laravel/breeze](https://github.com/laravel/breeze).

This package included:

- Login
- Register
- Forgot password - Reset password
- Verify email
- Confirm password

This package not included:

- Dashboard

Laravel Tailwind Authentication is powered by Blade and Tailwind.

Getting started couldn't be easier:

```bash
laravel new my-app

cd my-app

composer require phu1237/laravel-tailwind-auth --dev

php artisan auth:install
```

## Commands

Install package with default files

```bash
php artisan auth:install
```

Install with empty blade files

```bash
php artisan auth:install --empty
OR
php artisan auth:install -e
```

Install with only controllers

```bash
php artisan auth:install --controllers
OR
php artisan auth:install -c
```

## License

Laravel Tailwind Authentication is open-sourced software licensed under the [MIT license](LICENSE.md).
