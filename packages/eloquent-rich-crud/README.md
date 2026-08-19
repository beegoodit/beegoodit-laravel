# Eloquent Rich CRUD

Guard Eloquent `create()` / `delete()` on aggregate models and route those writes through `provision()` / `deprovision()`.

Naked Eloquent stays available for rows that are not aggregates. Factories that need a naked insert set `allowEloquentCreate` (for example in `afterMaking`). Trusted domain actions call `createAllowed()` / `deleteAllowed()`.

## Installation

```bash
composer require beegoodit/eloquent-rich-crud
```

This package has no migrations, service provider, or `lorisleiva/laravel-actions` dependency. Action classes only need a static `run()` method (laravel-actions `AsAction` is one way to get that).

## Usage

```php
use BeegoodIT\EloquentRichCrud\Provisionable;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use Provisionable;

    protected static function provisionActionClass(): string
    {
        return ProvisionProject::class;
    }
}

$project = Project::provision(['name' => 'Acme']);
```

Default action class names are `{model namespace}\Provision{ClassBasename}` and `{model namespace}\Deprovision{ClassBasename}`. Override `provisionActionClass()` / `deprovisionActionClass()` when the action lives elsewhere.

Bare `Model::create()` and `$model->delete()` throw `MustUseProvision` / `MustUseDeprovision` unless the hatch flags are set:

```php
$project = new Project(['name' => 'Acme']);
$project->createAllowed();

$project->deleteAllowed();
```
