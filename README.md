# Laravel Test Mail

[![Latest Version on Packagist](https://img.shields.io/packagist/v/resohead/laravel-test-mail.svg?style=flat-square)](https://packagist.org/packages/resohead/laravel-test-mail)
[![Build Status](https://img.shields.io/travis/resohead/laravel-test-mail/master.svg?style=flat-square)](https://travis-ci.org/resohead/laravel-test-mail)
[![Quality Score](https://img.shields.io/scrutinizer/g/resohead/laravel-test-mail.svg?style=flat-square)](https://scrutinizer-ci.com/g/resohead/laravel-test-mail)
[![Total Downloads](https://img.shields.io/packagist/dt/resohead/laravel-test-mail.svg?style=flat-square)](https://packagist.org/packages/resohead/laravel-test-mail)

A simple package to send test emails from artisan commands in Laravel applications. Ideal for checking mail and queue configurations.

## Installation

You can install the package via composer:

```bash
composer require resohead/laravel-test-mail
```

The package will automatically register itself.

## Usage

To send a test email run the following artisan command:

``` php
php artisan mail:test
```

By default this will use:
- the 'from' address defined in your mail config,
- your default mail driver,
- synchronous processing

Alternatively you have three other options in the command signature: 
- set the email address,
- change the mail driver,
- enable for queuing
- change the queue connection

Changing the mail driver and running through a queue might require the queue worker to be reset.

``` bash
// send using the default mail driver and default queue/stack to the specified email
php artisan mail:test name@example.com --queue

// queue using the 'log' mail driver
php artisan mail:test --driver=log

// queue using the 'emails' queue on the default connection
php artisan mail:test --stack=emails

// queue using the sqs queue connection, default queue and default mail driver
php artisan mail:test --connection=sqs

// send a test mail using the SMTP driver via the emails queue on the redis connection 
php artisan mail:test name@example.com --driver=smtp --connection=redis --stack=emails

```
> You might need to start the your queue if using the connection option, for example
```
php artisan queue:work sqs
```
## Alternatives

This is a simple package designed to quickly trigger an email to check your configuration. 

If you want to check what an email looks like in the browser use the Laravel documentation to [render mailables](https://laravel.com/docs/mail#rendering-mailables) (available since Laravel 5.5). 

If you need a package to send a mailable using fake data try using [Spatie's laravel-mailable-test package](https://github.com/spatie/laravel-mailable-test).

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Sean White](https://github.com/resohead)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
