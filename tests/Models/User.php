<?php

declare(strict_types=1);

namespace Elegantly\Conversation\Tests\Models;

use Elegantly\Conversation\Concerns\HasConversationsTrait;
use Illuminate\Foundation\Auth\User as Authentificate;

class User extends Authentificate
{
    use HasConversationsTrait;

    protected $table = 'users';
}
