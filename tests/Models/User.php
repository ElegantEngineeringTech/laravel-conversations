<?php

declare(strict_types=1);

namespace Elegantly\Conversation\Tests\Models;

use Elegantly\Conversation\Concerns\ParticipateToConversations;
use Illuminate\Foundation\Auth\User as Authentificate;

class User extends Authentificate
{
    use ParticipateToConversations;

    protected $table = 'users';
}
