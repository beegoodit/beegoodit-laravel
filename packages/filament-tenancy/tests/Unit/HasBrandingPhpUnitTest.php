<?php

namespace BeeGoodIT\FilamentTenancy\Tests\Unit;

use BeeGoodIT\FilamentTenancy\Models\Concerns\HasBranding;
use BeeGoodIT\FilamentTenancy\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HasBrandingPhpUnitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('s3');
    }

    public function test_it_generates_logo_url(): void
    {
        $team = new class extends Model
        {
            use HasBranding;

            public $logo = 'teams/logo/test.png';
        };

        Storage::disk('public')->put('teams/logo/test.png', 'logo data');

        $url = $team->getLogoUrl();

        $this->assertStringContainsString('teams/logo/test.png', $url);
    }

    public function test_it_returns_null_for_empty_logo(): void
    {
        $team = new class extends Model
        {
            use HasBranding;

            public $logo;
        };

        $this->assertNull($team->getLogoUrl());
    }

    public function test_it_provides_filament_logo_url(): void
    {
        $team = new class extends Model
        {
            use HasBranding;

            public $logo = 'teams/logo/test.png';
        };

        Storage::disk('public')->put('teams/logo/test.png', 'logo data');

        $this->assertEquals($team->getLogoUrl(), $team->getFilamentLogoUrl());
    }
}
