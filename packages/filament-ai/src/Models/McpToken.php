<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Models;

use BeegoodIT\FilamentAi\Support\FilamentAi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class McpToken extends Model
{
    public const SCOPE_READ = 'read';

    public const SCOPE_WRITE = 'write';

    protected $hidden = [
        'token_hash',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active_from' => 'datetime',
            'active_to' => 'datetime',
            'revoked_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return (string) config('filament-ai.mcp.tokens_table', 'beegoodit_filament_ai_mcp_tokens');
    }

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            FilamentAi::tenantColumn(),
            'created_by_user_id',
            'label',
            'token_hash',
            'scope',
            'active_from',
            'active_to',
            'revoked_at',
            'last_used_at',
        ];
    }

    /**
     * @return array{token: self, plain: string}
     */
    public static function issue(
        string $label,
        int|string|null $tenantId = null,
        int|string|null $createdByUserId = null,
        string $scope = self::SCOPE_READ,
        ?\DateTimeInterface $activeFrom = null,
        ?\DateTimeInterface $activeTo = null,
        int|string|null $siteId = null,
    ): array {
        $tenantId ??= $siteId;
        $prefix = (string) config('filament-ai.mcp.token_prefix', 'fa_mcp_');
        $plain = $prefix.Str::random(48);
        $column = FilamentAi::tenantColumn();

        $token = static::query()->create([
            $column => $tenantId,
            'created_by_user_id' => $createdByUserId,
            'label' => $label,
            'token_hash' => hash('sha256', $plain),
            'scope' => $scope,
            'active_from' => $activeFrom ?? now()->utc()->subYears(50),
            'active_to' => $activeTo ?? now()->utc()->addYear(),
        ]);

        return ['token' => $token, 'plain' => $plain];
    }

    public static function findByPlainToken(string $plain): ?self
    {
        $plain = trim($plain);

        if ($plain === '') {
            return null;
        }

        return static::query()
            ->where('token_hash', hash('sha256', $plain))
            ->first();
    }

    public function tenantKey(): int|string|null
    {
        return $this->getAttribute(FilamentAi::tenantColumn());
    }

    public function isCurrentlyValid(): bool
    {
        if ($this->revoked_at !== null) {
            return false;
        }

        $now = now()->utc();

        if ($this->active_from !== null && $now->lt($this->active_from)) {
            return false;
        }

        if ($this->active_to !== null && $now->gt($this->active_to)) {
            return false;
        }

        return true;
    }

    public function allowsWrite(): bool
    {
        return $this->scope === self::SCOPE_WRITE;
    }

    public function revoke(): void
    {
        if ($this->revoked_at !== null) {
            return;
        }

        $this->forceFill(['revoked_at' => now()])->save();
    }

    public function touchLastUsed(): void
    {
        $this->forceFill(['last_used_at' => now()])->save();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            (string) config('auth.providers.users.model'),
            'created_by_user_id',
        );
    }

    public function creatorDisplayName(): string
    {
        $user = $this->createdBy;

        if ($user === null) {
            return '—';
        }

        $name = trim((string) ($user->name ?? ''));

        if ($name !== '') {
            return $name;
        }

        $email = trim((string) ($user->email ?? ''));

        return $email !== '' ? $email : '—';
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForTenant(Builder $query, int|string|null $tenantId): Builder
    {
        $column = FilamentAi::tenantColumn();

        return $query
            ->when($tenantId !== null, fn (Builder $q) => $q->where($column, $tenantId))
            ->when($tenantId === null, fn (Builder $q) => $q->whereNull($column));
    }

    /**
     * TallCMS / soft-dep alias of {@see scopeForTenant()}.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForSite(Builder $query, int|string|null $siteId): Builder
    {
        return $this->scopeForTenant($query, $siteId);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }
}
