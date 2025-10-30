<?php

use BeeGoodIT\EloquentUserstamps\HasUserStamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test table
    Schema::create('test_models', function ($table) {
        $table->id();
        $table->string('name');
        $table->unsignedBigInteger('created_by_id')->nullable();
        $table->unsignedBigInteger('updated_by_id')->nullable();
        $table->timestamps();
    });
});

it('sets created_by_id when creating a model', function () {
    $user = createTestUser();
    $this->actingAs($user);
    
    $model = TestModel::create(['name' => 'Test']);
    
    expect($model->created_by_id)->toBe($user->id);
    expect($model->updated_by_id)->toBe($user->id);
});

it('sets updated_by_id when updating a model', function () {
    $creator = createTestUser();
    $updater = createTestUser();
    
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
    $user = createTestUser();
    $this->actingAs($user);
    
    $model = TestModel::create(['name' => 'Test']);
    
    expect($model->createdBy)->toBeInstanceOf(get_class($user));
    expect($model->createdBy->id)->toBe($user->id);
});

it('provides updatedBy relationship', function () {
    $user = createTestUser();
    $this->actingAs($user);
    
    $model = TestModel::create(['name' => 'Test']);
    
    expect($model->updatedBy)->toBeInstanceOf(get_class($user));
    expect($model->updatedBy->id)->toBe($user->id);
});

// Helper functions
function createTestUser()
{
    return \Illuminate\Foundation\Auth\User::forceCreate([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
}

class TestModel extends Model
{
    use HasUserStamps;
    
    protected $guarded = [];
}

