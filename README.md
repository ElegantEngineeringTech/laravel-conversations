
[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/support-ukraine.svg?t=1" />](https://supportukrainenow.org)

# This is my package laravel-conversations

[![Latest Version on Packagist](https://img.shields.io/packagist/v/finller/laravel-conversations.svg?style=flat-square)](https://packagist.org/packages/finller/laravel-conversations)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/finller/laravel-conversations/run-tests?label=tests)](https://github.com/finller/laravel-conversations/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/finller/laravel-conversations/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/finller/laravel-conversations/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/finller/laravel-conversations.svg?style=flat-square)](https://packagist.org/packages/finller/laravel-conversations)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require finller/laravel-conversations
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="conversations-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="conversations-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$conversation = new Finller\Conversation();
echo $conversation->echoPhrase('Hello, Finller!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/quentinGab/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Quentin Gabriele](https://github.com/quentinGab)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
