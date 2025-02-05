<?php

declare(strict_types=1);

namespace Elegantly\Conversation;

use Carbon\Carbon;
use Elegantly\Conversation\Concerns\HasUuid;
use Elegantly\Conversation\Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\HtmlString;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension;
use League\CommonMark\MarkdownConverter;

/**
 * @template TRead of Read
 * @template TConversation of Conversation
 * @template TUser of User
 *
 * @property int $id
 * @property string $uuid
 * @property ?string $origin
 * @property ?string $content
 * @property ?array{ component: string, props: array<array-key, mixed> } $widget
 * @property int $conversation_id
 * @property TConversation $conversation
 * @property ?int $user_id
 * @property ?TUser $user
 * @property Collection<int, Read> $reads
 * @property ?ArrayObject<array-key, mixed> $metadata
 * @property ?Carbon $created_at
 * @property ?Carbon $read_at
 * @property ?Carbon $deleted_at
 */
class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    use HasUuid;

    protected $guarded = [];

    protected $casts = [
        'metadata' => AsArrayObject::class,
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
        'widget' => 'array',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $message) {

            if (method_exists($message, 'isForceDeleting')) {
                if ($message->isForceDeleting()) {
                    $message->reads()->delete();
                }
            } else {
                $message->reads()->delete();
            }
        });
    }

    /**
     * @return class-string<TConversation>
     */
    public static function getModelConversation(): string
    {
        return config()->string('conversations.model_conversation');
    }

    /**
     * @return class-string<TUser>
     */
    public static function getModelUser(): string
    {
        return config()->string('conversations.model_user');
    }

    /**
     * @return class-string<TRead>
     */
    public static function getModelRead(): string
    {
        return config()->string('conversations.model_read');
    }

    /**
     * @return BelongsTo<TConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(static::getModelConversation());
    }

    /**
     * @return BelongsTo<TUser, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(static::getModelUser());
    }

    /**
     * @return HasMany<TRead, $this>
     */
    public function reads(): HasMany
    {
        return $this->hasMany(static::getModelRead());
    }

    public function markAsRead(): static
    {
        $this->read_at = now();

        return $this;
    }

    public function markAsUnread(): static
    {
        $this->read_at = null;

        return $this;
    }

    public function markAsReadBy(User|int $user, ?Carbon $date = null): static
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        $read = new Read;
        $read->user_id = $userId;
        $read->updated_at = $date;
        $read->created_at = $date;

        $this->reads()->save($read);

        if ($this->relationLoaded('reads')) {
            $this->reads->push($read);
        }

        return $this;
    }

    public function markAsUnreadBy(User|int $user): static
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        $read = $this->reads
            ->firstWhere('user_id', $userId);

        if ($read) {
            $read->delete();

            $this->setRelation(
                'reads',
                $this->reads->except([$read->id])
            );
        }

        return $this;
    }

    public function isReadBy(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        if ($this->user_id === $userId) {
            return true;
        }

        if ($this->read_at) {
            return true;
        }

        return (bool) $this->reads->firstWhere('user_id', $userId);
    }

    public function isReadByAnyone(): bool
    {
        return $this->read_at || $this->reads->isNotEmpty();
    }

    public function hasWidget(): bool
    {
        return (bool) $this->getWidgetComponent();
    }

    public function getWidgetComponent(): ?string
    {
        return data_get($this->widget, 'component');
    }

    /**
     * @param  array<array-key, mixed>  $props
     */
    public function setWidget(string $componentName, array $props): static
    {
        $this->widget = [
            'component' => $componentName,
            'props' => $props,
        ];

        return $this;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getWidgetProps(): array
    {
        return array_merge(data_get($this->widget, 'props', []), [
            'message' => $this,
        ]);
    }

    public static function markdown(?string $value): ?HtmlString
    {
        if (! $value) {
            return null;
        }

        $environment = new Environment(config('conversations.markdown.environment'));

        $environment->addExtension(new InlinesOnlyExtension);
        $environment->addExtension(new AutolinkExtension);
        $environment->addExtension(new ExternalLinkExtension);

        $converter = new MarkdownConverter($environment);

        return new HtmlString($converter->convert($value)->getContent());
    }

    public function toMarkdown(): ?HtmlString
    {
        return static::markdown($this->content);
    }
}
