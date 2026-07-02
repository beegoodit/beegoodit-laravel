<?php

use BeegoodIT\EloquentReadonlyAttributes\HasReadonlyAttributes;
use BeegoodIT\EloquentReadonlyAttributes\ReadonlyAttributes;
use BeegoodIT\EloquentReadonlyAttributes\ReadonlyAttributeViolation;
use BeegoodIT\EloquentReadonlyAttributes\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

uses(TestCase::class);

it('blocks dirty readonly attributes when their gate returns true', function (): void {
    $model = ReadonlyTestModel::query()->create([
        'name' => 'Original name',
        'status' => 'draft',
        'locked' => false,
    ]);

    $model->update(['locked' => true]);
    $model->status = 'published';

    expect(fn () => $model->save())
        ->toThrow(function (ReadonlyAttributeViolation $exception): bool {
            expect($exception)->toBeInstanceOf(ValidationException::class);
            expect($exception->attributes())->toBe(['status']);
            expect($exception->errors())->toHaveKey('status');

            return true;
        });
});

it('allows dirty guarded attributes when their gate returns false', function (): void {
    $model = ReadonlyTestModel::query()->create([
        'name' => 'Original name',
        'status' => 'draft',
        'locked' => false,
    ]);

    $model->update(['status' => 'published']);

    expect($model->refresh()->status)->toBe('published');
});

it('reports every dirty readonly attribute that violates its gate', function (): void {
    $model = ReadonlyTestModel::query()->create([
        'name' => 'Original name',
        'status' => 'draft',
        'locked' => false,
        'readonly_name' => false,
    ]);

    $model->update([
        'locked' => true,
        'readonly_name' => true,
    ]);
    $model->name = 'New name';
    $model->status = 'published';

    try {
        $model->save();
    } catch (ReadonlyAttributeViolation $exception) {
        expect($exception->attributes())->toBe(['name', 'status']);

        return;
    }

    test()->fail('Expected readonly attribute violation was not thrown.');
});

it('allows writes while readonly guards are bypassed', function (): void {
    $model = ReadonlyTestModel::query()->create([
        'name' => 'Original name',
        'status' => 'draft',
        'locked' => false,
    ]);

    $model->update(['locked' => true]);
    ReadonlyAttributes::withoutGuards(fn (): bool => $model->update(['status' => 'published']));

    expect($model->refresh()->status)->toBe('published');
});

it('restores readonly guards after nested bypass callbacks', function (): void {
    $model = ReadonlyTestModel::query()->create([
        'name' => 'Original name',
        'status' => 'draft',
        'locked' => false,
    ]);

    $model->update(['locked' => true]);
    ReadonlyAttributes::withoutGuards(function () use ($model): void {
        ReadonlyAttributes::withoutGuards(fn (): bool => $model->update(['status' => 'published']));
    });

    $model->status = 'archived';

    expect(fn () => $model->save())->toThrow(ReadonlyAttributeViolation::class);
});

it('does not block Laravel timestamp housekeeping attributes', function (): void {
    $model = ReadonlyTestModel::query()->create([
        'name' => 'Original name',
        'status' => 'draft',
        'description' => 'Before',
        'locked' => false,
    ]);

    $model->update(['description' => 'After']);

    expect($model->refresh()->description)->toBe('After');
});

it('maps readonly violations to a nested form state path', function (): void {
    $exception = ReadonlyAttributeViolation::forAttributes(['published_at', 'start_at']);

    expect($exception->errorsForStatePath('data'))->toBe([
        'data.published_at' => [$exception->errors()['published_at'][0]],
        'data.start_at' => [$exception->errors()['start_at'][0]],
    ]);
});

class ReadonlyTestModel extends Model
{
    use HasReadonlyAttributes;

    protected $table = 'readonly_test_models';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'locked' => 'bool',
            'readonly_name' => 'bool',
        ];
    }

    protected function readonlyAttributes(): array
    {
        return [
            'name' => fn (self $model): bool => (bool) $model->readonly_name,
            'status' => fn (self $model): bool => (bool) $model->locked,
            'updated_at' => fn (self $model): bool => true,
        ];
    }
}
