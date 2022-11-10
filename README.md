
# Attach a conversation to any model and easily create a chat 

[![Latest Version on Packagist](https://img.shields.io/packagist/v/finller/laravel-conversations.svg?style=flat-square)](https://packagist.org/packages/finller/laravel-conversations)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/finller/laravel-conversations/run-tests?label=tests)](https://github.com/finller/laravel-conversations/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/finller/laravel-conversations/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/finller/laravel-conversations/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/finller/laravel-conversations.svg?style=flat-square)](https://packagist.org/packages/finller/laravel-conversations)

This package the basic architecture of a chat between multiple users.

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

- [Quentin Gabriele](https://github.com/quentinGab)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
