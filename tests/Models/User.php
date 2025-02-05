<?php

declare(strict_types=1);

namespace Finller\Conversation\Tests\Models;

use Finller\Conversation\Concerns\ParticipateToConversations;
use Illuminate\Foundation\Auth\User as Authentificate;

class User extends Authentificate
{
    use ParticipateToConversations;

    protected $table = 'users';
}
