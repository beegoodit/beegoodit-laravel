<?php

declare(strict_types=1);

namespace BeegoodIT\LaravelPublicResources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * @mixin Model
 */
trait HasPublicId
{
    protected static function bootHasPublicId(): void
    {
        static::creating(function (Model $model): void {
            /** @var Model&HasPublicId $model */
            $column = $model->publicIdColumn();

            if (filled($model->getAttribute($column))) {
                $model->setAttribute(
                    $column,
                    PublicId::assertValid((string) $model->getAttribute($column), $model->publicIdLength())
                );

                return;
            }

            $model->setAttribute($column, $model->generateUniquePublicId());
        });
    }

    public function initializeHasPublicId(): void
    {
        $this->mergeFillable([$this->publicIdColumn()]);
    }

    public function publicIdColumn(): string
    {
        return 'public_id';
    }

    public function publicIdLength(): int
    {
        return PublicId::LENGTH;
    }

    /**
     * Columns that scope uniqueness together with public_id (e.g. ['site_id']).
     *
     * @return list<string>
     */
    public function publicIdUniquenessColumns(): array
    {
        return [];
    }

    public function publicIdGenerationAttempts(): int
    {
        return 16;
    }

    public function getPublicId(): ?string
    {
        $value = $this->getAttribute($this->publicIdColumn());

        return $value !== null ? (string) $value : null;
    }

    public function publicResourceKey(?string $slug = null): string
    {
        $publicId = $this->getPublicId();

        if ($publicId === null || $publicId === '') {
            throw new RuntimeException(sprintf('%s has no public_id.', $this::class));
        }

        return PublicResourceKey::format($slug, $publicId, $this->publicIdLength());
    }

    protected function generateUniquePublicId(): string
    {
        $attempts = $this->publicIdGenerationAttempts();

        for ($i = 0; $i < $attempts; $i++) {
            $candidate = $this->newPublicIdCandidate();

            if (! $this->publicIdExists($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException(sprintf(
            'Unable to generate a unique public_id for %s after %d attempts.',
            $this::class,
            $attempts
        ));
    }

    protected function newPublicIdCandidate(): string
    {
        return PublicId::generate($this->publicIdLength());
    }

    protected function publicIdExists(string $publicId): bool
    {
        $query = $this->newQueryWithoutScopes()
            ->where($this->publicIdColumn(), $publicId);

        $this->applyPublicIdUniquenessScope($query);

        return $query->exists();
    }

    /**
     * @param  Builder<Model>  $query
     */
    protected function applyPublicIdUniquenessScope(Builder $query): void
    {
        foreach ($this->publicIdUniquenessColumns() as $column) {
            $query->where($column, $this->getAttribute($column));
        }
    }
}
