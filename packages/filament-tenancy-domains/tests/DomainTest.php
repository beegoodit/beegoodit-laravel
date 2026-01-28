<?php

namespace BeegoodIT\FilamentTenancyDomains\Tests;

use BeegoodIT\FilamentTenancyDomains\HasDomains;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TestTour extends Model
{
    use HasDomains, HasUuids;

    protected $table = 'tours';

    protected $fillable = ['name', 'slug'];
}

class DomainTest extends TestCase
{
    public function test_it_can_be_associated_with_a_model(): void
    {
        $tour = TestTour::create([
            'name' => 'Test Tour',
            'slug' => 'test-tour',
        ]);

        $domain = $tour->domains()->create([
            'domain' => 'tour.foosbeaver.app',
            'type' => 'platform',
        ]);

        $this->assertCount(1, $tour->domains);
        $this->assertEquals('tour.foosbeaver.app', $tour->domains->first()->domain);
        $this->assertInstanceOf(TestTour::class, $domain->model);
        $this->assertEquals($tour->id, $domain->model->id);
    }

    public function test_it_can_have_a_primary_domain(): void
    {
        $tour = TestTour::create([
            'name' => 'Test Tour',
            'slug' => 'test-tour',
        ]);

        $tour->domains()->create([
            'domain' => 'tour.foosbeaver.app',
            'type' => 'platform',
            'is_primary' => true,
        ]);

        $this->assertNotNull($tour->primaryDomain);
        $this->assertEquals('tour.foosbeaver.app', $tour->primaryDomain->domain);
    }
}
