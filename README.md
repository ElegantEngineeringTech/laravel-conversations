# Attach a conversation to any model and easily create a chat

[![Latest Version on Packagist](https://img.shields.io/packagist/v/elegantly/laravel-conversation.svg)](https://packagist.org/packages/elegantly/laravel-conversation)
[![Total Downloads](https://img.shields.io/packagist/dt/elegantly/laravel-conversation.svg)](https://packagist.org/packages/elegantly/laravel-conversation)
[![Tests](https://github.com/ElegantEngineeringTech/laravel-conversation/actions/workflows/run-tests.yml/badge.svg)](https://github.com/ElegantEngineeringTech/laravel-conversation/actions/workflows/run-tests.yml)
[![Laravel Pint](https://github.com/ElegantEngineeringTech/laravel-conversation/actions/workflows/pint.yml/badge.svg)](https://github.com/ElegantEngineeringTech/laravel-conversation/actions/workflows/pint.yml)
[![PHPStan](https://github.com/ElegantEngineeringTech/laravel-conversation/actions/workflows/phpstan.yml/badge.svg)](https://github.com/ElegantEngineeringTech/laravel-conversation/actions/workflows/phpstan.yml)


This package provides the backend for building chat supporting multiple users and granular reads.

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

$this->conversation->send($message);

```

### 3. Query unread conversations

```php

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
