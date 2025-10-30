<?php

use BeeGoodIT\FilamentTeamBranding\Models\Concerns\HasBranding;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('s3');
});

it('generates logo URL', function () {
    $team = new class extends Model {
        use HasBranding;
        public $logo = 'teams/logo/test.png';
    };
    
    Storage::disk('public')->put('teams/logo/test.png', 'logo data');
    
    $url = $team->getLogoUrl();
    
    expect($url)->toContain('teams/logo/test.png');
});

it('returns null for empty logo', function () {
    $team = new class extends Model {
        use HasBranding;
        public $logo = null;
    };
    
    expect($team->getLogoUrl())->toBeNull();
});

it('provides filament logo URL', function () {
    $team = new class extends Model {
        use HasBranding;
        public $logo = 'teams/logo/test.png';
    };
    
    Storage::disk('public')->put('teams/logo/test.png', 'logo data');
    
    expect($team->getFilamentLogoUrl())->toBe($team->getLogoUrl());
});

