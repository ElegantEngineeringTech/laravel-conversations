# Attach a conversation to any model and easily create a chat

[![Latest Version on Packagist](https://img.shields.io/packagist/v/elegantly/laravel-conversations.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-conversations)
[![Tests](https://img.shields.io/github/actions/workflow/status/ElegantEngineeringTech/laravel-conversations/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/ElegantEngineeringTech/laravel-conversations/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Code Style](https://img.shields.io/github/actions/workflow/status/ElegantEngineeringTech/laravel-conversations/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/ElegantEngineeringTech/laravel-conversations/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![PHPStan Level](https://img.shields.io/github/actions/workflow/status/ElegantEngineeringTech/laravel-conversations/phpstan.yml?label=phpstan&style=flat-square)](https://github.com/ElegantEngineeringTech/laravel-conversations/actions?query=workflow%3Aphpstan)
[![Laravel Pint](https://img.shields.io/github/actions/workflow/status/ElegantEngineeringTech/laravel-conversations/pint.yml?label=laravel%20pint&style=flat-square)](https://github.com/ElegantEngineeringTech/laravel-conversations/actions?query=workflow%3Apint)
[![Total Downloads](https://img.shields.io/packagist/dt/elegantly/laravel-conversations.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-conversations)

This package the basic architecture of a chat between multiple users.

## Installation

You can install the package via composer:

```bash
composer require elegantly/laravel-conversations
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

    /**
     * The Model used with the user_id and owner_id
     */
    'model_user' => User::class,

    'model_message' => Message::class,

    'model_conversation' => Conversation::class,

    /**
     * When a User is deleted, his messages will be deleted
     */
    'cascade_user_delete_to_messages' => false,

    /**
     * When a User is deleted, his messages will be deleted
     */
    'cascade_conversation_delete_to_messages' => false,

    /**
     * When the parent of a conversation is deleted, the conversation is deleted
     */
    'cascade_conversationable_delete_to_conversation' => false,
];
```

## Usage

### 1. Create the conversation

```php
$conversation = new Conversation();

$conversation->conversationable()->associate($mission); // optional

$conversation->save();

$conversation->users()->sync($usersIds);

```

### 2. Save messages in the conversation

```php

$message = new Message([
    'content' => "My message",
]);

$message->user()->associate($this->user);

$this->conversation->messages()->save($message);

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

-   [Quentin Gabriele](https://github.com/quentinGab)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
