# Eloquent Readonly Attributes

Conditionally prevent selected Eloquent attributes from being changed when a model is saved.

## Installation

Install the package with Composer:

```bash
composer require beegoodit/eloquent-readonly-attributes
```

For BeegoodIT applications, the existing path repository can resolve the package from `packages/eloquent-readonly-attributes`.

## Usage

Add the `HasReadonlyAttributes` trait to a model and return one gate per guarded attribute from `readonlyAttributes()`.

```php
use BeegoodIT\EloquentReadonlyAttributes\HasReadonlyAttributes;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasReadonlyAttributes;

    /**
     * @return array<string, \Closure(static): bool>
     */
    protected function readonlyAttributes(): array
    {
        return [
            'start_at' => fn (self $model): bool => $model->isFrozen(),
            'location_id' => fn (self $model): bool => $model->isFrozen(),
        ];
    }
}
```

When `saving` runs, including during creation, the trait intersects the model's dirty attributes with the configured gates. If a dirty attribute's gate returns `true`, saving is aborted with `ReadonlyAttributeViolation`, a `ValidationException` with field-level error messages that HTTP clients and Filament can render as validation errors.

Unlisted attributes are not guarded. Laravel housekeeping timestamps such as `created_at` and `updated_at` are skipped so normal saves can continue.

## Bypassing Guards

Use `ReadonlyAttributes::withoutGuards()` for trusted maintenance or migration code that must intentionally write readonly attributes:

```php
use BeegoodIT\EloquentReadonlyAttributes\ReadonlyAttributes;

ReadonlyAttributes::withoutGuards(function () use ($tournament): void {
    $tournament->update(['start_at' => now()->addWeek()]);
});
```

Bypasses are stacked, so nested callbacks keep guards disabled until the outermost callback exits.
