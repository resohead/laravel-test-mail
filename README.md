# Laravel Test Mail

[![Latest Version on Packagist](https://img.shields.io/packagist/v/resohead/laravel-test-mail.svg?style=flat-square)](https://packagist.org/packages/resohead/laravel-test-mail)
[![Quality Score](https://img.shields.io/scrutinizer/g/resohead/laravel-test-mail.svg?style=flat-square)](https://scrutinizer-ci.com/g/resohead/laravel-test-mail)
[![Maintainability](https://api.codeclimate.com/v1/badges/ddf2b91c4d6c595d6ff0/maintainability)](https://codeclimate.com/github/resohead/laravel-test-mail/maintainability)
[![Total Downloads](https://img.shields.io/packagist/dt/resohead/laravel-test-mail.svg?style=flat-square)](https://packagist.org/packages/resohead/laravel-test-mail)

A simple package to send test emails from artisan commands in Laravel applications. Ideal for checking mail and queue configurations.

## Installation

You can install the package via composer:

```bash
composer require resohead/laravel-test-mail
```

The package will automatically register itself.

Optionally publish the config file:
```
php artisan vendor:publish --provider="Resohead\LaravelTestMail\LaravelTestMailServiceProvider" --tag="config"
```

## Basic Usage

To send a test email run the following artisan command:

``` php
php artisan mail:test
```

By default this will use:
- the 'from' address defined in your mail config,
- your default mail driver,
- synchronous processing

Alternatively you have four other options in the command signature: 
- set the email address,
- change the mail driver,
- enable for queuing
- change the queue connection
- select the preset

> Changing the mail driver and running through a queue might require the queue worker to be started/reset.

## Command Line Options

### Send to specified email
```
php artisan mail:test name@example.com
```

### Send to specified email on default queue
```
php artisan mail:test name@example.com --queue
```

### Send via log driver 
```
php artisan mail:test --driver=log
```

### Send to the 'emails' queue on default connection 
```
php artisan mail:test --stack=emails
```
> There is no need to set the --queue flag when using the stack argument

### Send using 'sqs' queue connection
```
php artisan mail:test --connection=sqs
```
> There is no need to set the --queue flag when using the connection argument

### Send using the SMTP driver via the 'emails' queue on the 'redis' connection 
```
php artisan mail:test name@example.com --driver=smtp --connection=redis --stack=emails
```

## Queues
> You might need to start the your queue if using the connection option, for example
```
php artisan queue:work sqs --queue:emails
```

## Presets

You can also configure presets to group command line options. The values defined in each preset will be merged with the command line values and your default mail and queue configuration.

### Example config\mail-test.php

```
'presets' => [

        'example1' => [
            'recipient' => 'preset1@example.com',
            'queue' => true
        ],

        'example2' => [
            'driver' => 'log',
            'stack' => 'emails'
        ],

        'example3' => [
            'recipient' => env('EMAIL_TO', 'preset3@example.com'),
            'driver' => 'smtp',
            'connection' => 'redis',
            'stack' => 'notifications'
        ],

    ]
```

### Preset: Example 1
Set a specific email address and use default queue:
```
php artisan mail:test --preset=example1

// php artisan mail:test preset1@example.com --queue
```

### Preset: Example 2
Use the log mail driver and emails queue
```
php artisan mail:test --preset=example2

// php artisan mail:test --driver=log --stack=emails
```

### Preset: Example 3
Use the log mail driver and emails queue
```
php artisan mail:test --preset=example3

// php artisan mail:test preset3@example.com --driver=smtp --connection=redis --stack=notifications
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
