<?php

namespace BeegoodIT\EloquentRichCrud;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use RuntimeException;

/**
 * Block bare Eloquent create()/delete() unless createAllowed()/deleteAllowed() opted in.
 *
 * @mixin Model
 */
trait Provisionable
{
    public bool $allowEloquentCreate = false;

    public bool $allowEloquentDelete = false;

    protected static function bootProvisionable(): void
    {
        static::creating(function (Model $model): void {
            /** @var Model&Provisionable $model */
            if ($model->allowEloquentCreate) {
                return;
            }

            throw MustUseProvision::for($model);
        });

        static::deleting(function (Model $model): void {
            /** @var Model&Provisionable $model */
            if ($model->allowEloquentDelete) {
                return;
            }

            throw MustUseDeprovision::for($model);
        });
    }

    public static function provision(mixed ...$arguments): mixed
    {
        return static::runDomainAction('provision', ...$arguments);
    }

    public function deprovision(mixed ...$arguments): mixed
    {
        return static::runDomainAction('deprovision', $this, ...$arguments);
    }

    /**
     * Persist this unsaved model past the creating guard.
     */
    public function createAllowed(): bool
    {
        $this->allowEloquentCreate = true;

        return $this->save();
    }

    /**
     * Delete this model past the deleting guard.
     */
    public function deleteAllowed(): bool
    {
        $this->allowEloquentDelete = true;

        return (bool) $this->delete();
    }

    /**
     * @return class-string
     */
    protected static function provisionActionClass(): string
    {
        return static::domainActionClass('Provision');
    }

    /**
     * @return class-string
     */
    protected static function deprovisionActionClass(): string
    {
        return static::domainActionClass('Deprovision');
    }

    /**
     * @return class-string
     */
    protected static function domainActionClass(string $verb): string
    {
        return (new ReflectionClass(static::class))->getNamespaceName()
            .'\\'.$verb.class_basename(static::class);
    }

    protected static function runDomainAction(string $verb, mixed ...$arguments): mixed
    {
        $class = $verb === 'provision'
            ? static::provisionActionClass()
            : static::deprovisionActionClass();

        if (! class_exists($class) || ! method_exists($class, 'run')) {
            throw new RuntimeException(sprintf(
                'Missing %s action [%s] for %s.',
                $verb,
                $class,
                static::class,
            ));
        }

        return $class::run(...$arguments);
    }
}
