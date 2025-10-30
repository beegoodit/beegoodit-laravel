<?php

use BeeGoodIT\EloquentUserstamps\HasUserStamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

// Define test model
class TestModel extends Model
{
    use HasUserStamps;
    
    protected $connection = 'testing';
    protected $guarded = [];
}

it('sets created_by_id when creating a model', function () {
    // Ensure database is ready
    if (! $this->app->bound('db')) {
        $this->fail('Database not bound in app container');
    }
    
    Model::unguard();
    
    // Use DB facade to insert user directly to avoid model issues
    $userId = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $user = Authenticatable::find($userId);
    $this->actingAs($user);
    
    $model = TestModel::create(['name' => 'Test']);
    
    expect($model->created_by_id)->toBe($user->id);
    expect($model->updated_by_id)->toBe($user->id);
});

it('sets updated_by_id when updating a model', function () {
    $creator = Authenticatable::forceCreate([
        'name' => 'Creator',
        'email' => 'creator@example.com',
        'password' => 'password',
    ]);
    
    $updater = Authenticatable::forceCreate([
        'name' => 'Updater',
        'email' => 'updater@example.com',
        'password' => 'password',
    ]);
    
    $this->actingAs($creator);
    $model = TestModel::create(['name' => 'Test']);
    
    $this->actingAs($updater);
    $model->update(['name' => 'Updated']);
    
    expect($model->created_by_id)->toBe($creator->id);
    expect($model->updated_by_id)->toBe($updater->id);
});

it('does not set userstamps when user is not authenticated', function () {
    $model = TestModel::create(['name' => 'Test']);
    
    expect($model->created_by_id)->toBeNull();
    expect($model->updated_by_id)->toBeNull();
});

it('provides createdBy relationship', function () {
    $user = Authenticatable::forceCreate([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);
    
    $this->actingAs($user);
    
    $model = TestModel::create(['name' => 'Test']);
    
    expect($model->createdBy)->toBeInstanceOf(Authenticatable::class);
    expect($model->createdBy->id)->toBe($user->id);
});

it('provides updatedBy relationship', function () {
    $user = Authenticatable::forceCreate([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);
    
    $this->actingAs($user);
    
    $model = TestModel::create(['name' => 'Test']);
    
    expect($model->updatedBy)->toBeInstanceOf(Authenticatable::class);
    expect($model->updatedBy->id)->toBe($user->id);
});

