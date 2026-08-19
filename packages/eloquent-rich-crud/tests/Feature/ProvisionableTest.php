<?php

use BeegoodIT\EloquentRichCrud\MustUseDeprovision;
use BeegoodIT\EloquentRichCrud\MustUseProvision;
use BeegoodIT\EloquentRichCrud\Provisionable;
use BeegoodIT\EloquentRichCrud\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;

uses(TestCase::class);

it('blocks bare create unless createAllowed is used', function (): void {
    expect(fn () => ProvisionableTestModel::query()->create(['name' => 'Blocked']))
        ->toThrow(MustUseProvision::class);
});

it('blocks bare delete unless deleteAllowed is used', function (): void {
    $model = ProvisionableTestModel::provision(['name' => 'Keep']);

    expect(fn () => $model->delete())->toThrow(MustUseDeprovision::class);
});

it('provisions through the derived action and createAllowed hatch', function (): void {
    $model = ProvisionableTestModel::provision(['name' => 'Acme']);

    expect($model->exists)->toBeTrue()
        ->and($model->name)->toBe('Acme');
});

it('deprovisions through the derived action and deleteAllowed hatch', function (): void {
    $model = ProvisionableTestModel::provision(['name' => 'Gone']);

    $model->deprovision();

    expect(ProvisionableTestModel::query()->whereKey($model->getKey())->exists())->toBeFalse();
});

it('allows factory-style naked create when allowEloquentCreate is set', function (): void {
    $model = new ProvisionableTestModel(['name' => 'Factory']);
    $model->allowEloquentCreate = true;
    $model->save();

    expect($model->exists)->toBeTrue();
});

it('derives action class names from the model namespace', function (): void {
    expect(ConventionTestModel::provision(['name' => 'Convention'])->name)->toBe('Convention');
});

it('fails clearly when the provision action class is missing', function (): void {
    expect(fn () => MissingActionTestModel::provision(['name' => 'Nope']))
        ->toThrow(RuntimeException::class, 'Missing provision action');
});

class ProvisionableTestModel extends Model
{
    use Provisionable;

    protected $table = 'provisionable_test_models';

    protected $guarded = [];

    protected static function provisionActionClass(): string
    {
        return ProvisionProvisionableTestModel::class;
    }

    protected static function deprovisionActionClass(): string
    {
        return DeprovisionProvisionableTestModel::class;
    }
}

class ProvisionProvisionableTestModel
{
    public static function run(array $attributes): ProvisionableTestModel
    {
        $model = new ProvisionableTestModel($attributes);
        $model->createAllowed();

        return $model;
    }
}

class DeprovisionProvisionableTestModel
{
    public static function run(ProvisionableTestModel $model): bool
    {
        return $model->deleteAllowed();
    }
}

class ConventionTestModel extends Model
{
    use Provisionable;

    protected $table = 'provisionable_test_models';

    protected $guarded = [];
}

class ProvisionConventionTestModel
{
    public static function run(array $attributes): ConventionTestModel
    {
        $model = new ConventionTestModel($attributes);
        $model->createAllowed();

        return $model;
    }
}

class MissingActionTestModel extends Model
{
    use Provisionable;

    protected $table = 'provisionable_test_models';

    protected $guarded = [];
}
