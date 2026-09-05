<?php

declare(strict_types=1);

namespace Elegantly\Conversation;

use Carbon\CarbonInterface;
use Elegantly\Conversation\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
 * @template TUser of User
 * @template TConversation of Conversation
 * @template TMessageRead of MessageRead
 *
 * @property int $id
 * @property string $uuid
 * @property ?string $origin
 * @property ?string $content
 * @property ?array{ component: string, props: array<array-key, mixed> } $widget
 * @property int $conversation_id
 * @property-read TConversation $conversation
 * @property ?int $user_id
 * @property-read ?TUser $user
 * @property-read Collection<int, TMessageRead> $reads
 * @property ?array<array-key, mixed> $metadata
 * @property ?CarbonInterface $deleted_at
 * @property CarbonInterface $updated_at
 * @property CarbonInterface $created_at
 */
class Message extends Model
{
    use HasUuid;

    protected $guarded = ['id', 'uuid'];

    public function casts()
    {
        return [
            'metadata' => 'array',
            'deleted_at' => 'datetime',
            'widget' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $message) {

            if (! method_exists($message, 'isForceDeleting') || $message->isForceDeleting()) {
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
     * @return class-string<TMessageRead>
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
     * @return HasMany<TMessageRead, $this>
     */
    public function reads(): HasMany
    {
        return $this->hasMany(static::getModelRead());
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeByUser(Builder $query, User|int|null $user): Builder
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        if ($userId === null) {
            return $query->whereNull('user_id');
        }

        return $query->where('user_id', $userId);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeNotByUser(Builder $query, User|int|null $user): Builder
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        if ($userId === null) {
            return $query->whereNotNull('user_id');
        }

        return $query->where(function ($query) use ($userId) {
            return $query
                ->whereNull('user_id')
                ->orWhere('user_id', '!=', $userId);
        });
    }

    public function markAsReadBy(
        User|int $user,
        ?CarbonInterface $date = null,
    ): static {
        $userId = $user instanceof User ? $user->getKey() : $user;

        $read = static::getModelRead()::query()->firstOrNew([
            'user_id' => $userId,
            'message_id' => $this->id,
        ]);

        if ($date) {
            $read->read_at = clone $date;
        } else {
            $read->read_at ??= now();
        }

        $read->save();

        if ($this->relationLoaded('reads')) {
            $this->setRelation(
                'reads',
                $this->reads->except([$read->id])->push($read)
            );
        }

        return $this;
    }

    public function markAsUnreadBy(User|int $user): static
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        $read = static::getModelRead()::query()
            ->where('user_id', $userId)
            ->where('message_id', $this->id)
            ->first();

        if ($read === null) {
            return $this;
        }

        $read->read_at = null;
        $read->save();

        if ($this->relationLoaded('reads')) {
            $this->setRelation(
                'reads',
                $this->reads->except([$read->id])->push($read)
            );
        }

        return $this;
    }

    /**
     * @return TMessageRead
     */
    public function getReadBy(User|int $user): ?MessageRead
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return $this->reads->firstWhere('user_id', $userId);
    }

    public function getReadByAt(User|int $user): ?CarbonInterface
    {
        return $this->getReadBy($user)?->read_at;
    }

    public function isReadBy(User|int $user): bool
    {
        return (bool) $this->getReadByAt($user);
    }

    public function isNotReadBy(User|int $user): bool
    {
        return ! $this->isReadBy($user);
    }

    /**
     * @param  null|int[]  $except
     */
    public function isReadByAnyone(?array $except = null): bool
    {
        return $this->reads
            ->except($except)
            ->some('read_at', '!=', null);
    }

    /**
     * @param  int[]  $users
     */
    public function isReadByAll(array $users = []): bool
    {
        foreach ($users as $user) {
            if ($this->isNotReadBy($user)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeUnreadBy(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return $query->whereDoesntHave(
            'reads',
            fn ($query) => $query
                ->where('user_id', $userId)
                ->whereNotNull('read_at')
        );
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeReadBy(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return $query->whereHas(
            'reads',
            fn ($query) => $query
                ->where('user_id', $userId)
                ->whereNotNull('read_at')
        );
    }

    public function markAsReadAndNotifiedTo(
        User|int $user,
        ?string $channel = null,
        ?CarbonInterface $date = null,
    ): static {
        $userId = $user instanceof User ? $user->getKey() : $user;

        $read = static::getModelRead()::query()->firstOrNew([
            'user_id' => $userId,
            'message_id' => $this->id,
        ]);

        if ($date) {
            $read->read_at = clone $date;
            $read->notified_at = clone $date;
            $read->notified_channel = $channel;
        } else {
            $read->read_at ??= now();
            $read->notified_at ??= now();
            $read->notified_channel ??= $channel;
        }

        $read->save();

        if ($this->relationLoaded('reads')) {
            $this->setRelation(
                'reads',
                $this->reads->except([$read->id])->push($read)
            );
        }

        return $this;
    }

    public function markAsNotifiedTo(
        User|int $user,
        ?string $channel = null,
        ?CarbonInterface $date = null,
    ): static {
        $userId = $user instanceof User ? $user->getKey() : $user;

        $read = static::getModelRead()::query()->firstOrNew([
            'user_id' => $userId,
            'message_id' => $this->id,
        ]);

        if ($date) {
            $read->notified_at = clone $date;
            $read->notified_channel = $channel;
        } else {
            $read->notified_at ??= now();
            $read->notified_channel ??= $channel;
        }

        $read->save();

        if ($this->relationLoaded('reads')) {
            $this->setRelation(
                'reads',
                $this->reads->except([$read->id])->push($read)
            );
        }

        return $this;
    }

    public function markAsUnNotifiedTo(User|int $user): static
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        $read = static::getModelRead()::query()
            ->where('user_id', $userId)
            ->where('message_id', $this->id)
            ->first();

        if ($read === null) {
            return $this;
        }

        $read->notified_at = null;
        $read->notified_channel = null;
        $read->save();

        if ($this->relationLoaded('reads')) {
            $this->setRelation(
                'reads',
                $this->reads->except([$read->id])->push($read)
            );
        }

        return $this;
    }

    public function getNotifiedToAt(User|int $user): ?CarbonInterface
    {
        return $this->getReadBy($user)?->notified_at;
    }

    public function isNotifiedTo(User|int $user): bool
    {
        return (bool) $this->getNotifiedToAt($user);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeUnNotifiedTo(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return $query->whereDoesntHave(
            'reads',
            fn ($query) => $query
                ->where('user_id', $userId)
                ->whereNotNull('notified_at')
        );
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeNotifiedTo(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return $query->whereHas(
            'reads',
            fn ($query) => $query
                ->where('user_id', $userId)
                ->whereNotNull('notified_at')
        );
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
        if ($value === null) {
            return $value;
        }

        if (blank($value)) {
            return new HtmlString($value);
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
