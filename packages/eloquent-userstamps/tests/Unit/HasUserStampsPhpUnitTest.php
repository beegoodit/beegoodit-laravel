<?php

namespace BeeGoodIT\EloquentUserstamps\Tests\Unit;

use BeeGoodIT\EloquentUserstamps\HasUserStamps;
use BeeGoodIT\EloquentUserstamps\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasUserStampsPhpUnitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();

        // Manually run migrations since defineDatabaseMigrations might not be called
        $this->loadLaravelMigrations();

        // Create test table
        \Illuminate\Support\Facades\Schema::create('test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->unsignedBigInteger('updated_by_id')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_sets_created_by_id_when_creating_a_model()
    {
        $user = Authenticatable::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($user);

        $model = TestModelPhpUnit::create(['name' => 'Test']);

        $this->assertEquals($user->id, $model->created_by_id);
        $this->assertEquals($user->id, $model->updated_by_id);
    }

    public function test_it_sets_updated_by_id_when_updating_a_model()
    {
        $creator = Authenticatable::create([
            'name' => 'Creator',
            'email' => 'creator@example.com',
            'password' => 'password',
        ]);

        $updater = Authenticatable::create([
            'name' => 'Updater',
            'email' => 'updater@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($creator);
        $model = TestModelPhpUnit::create(['name' => 'Test']);

        $this->actingAs($updater);
        $model->update(['name' => 'Updated']);

        $this->assertEquals($creator->id, $model->created_by_id);
        $this->assertEquals($updater->id, $model->updated_by_id);
    }

    public function test_it_does_not_set_userstamps_when_user_is_not_authenticated()
    {
        $model = TestModelPhpUnit::create(['name' => 'Test']);

        $this->assertNull($model->created_by_id);
        $this->assertNull($model->updated_by_id);
    }

    public function test_it_provides_created_by_relationship()
    {
        $user = Authenticatable::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($user);

        $model = TestModelPhpUnit::create(['name' => 'Test']);

        $this->assertInstanceOf(Authenticatable::class, $model->createdBy);
        $this->assertEquals($user->id, $model->createdBy->id);
    }

    public function test_it_provides_updated_by_relationship()
    {
        $user = Authenticatable::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($user);

        $model = TestModelPhpUnit::create(['name' => 'Test']);

        $this->assertInstanceOf(Authenticatable::class, $model->updatedBy);
        $this->assertEquals($user->id, $model->updatedBy->id);
    }
}

class TestModelPhpUnit extends Model
{
    use HasUserStamps;

    protected $table = 'test_models';

    protected $guarded = [];
}
