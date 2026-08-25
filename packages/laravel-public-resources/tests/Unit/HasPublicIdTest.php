<?php

declare(strict_types=1);

namespace BeegoodIT\LaravelPublicResources\Tests\Unit;

use BeegoodIT\LaravelPublicResources\HasPublicId;
use BeegoodIT\LaravelPublicResources\PublicId;
use BeegoodIT\LaravelPublicResources\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;

class HasPublicIdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
    }

    public function test_assigns_public_id_on_create(): void
    {
        $model = SiteScopedPublicIdModel::query()->create([
            'site_id' => 1,
            'name' => 'Event',
        ]);

        $this->assertNotNull($model->public_id);
        $this->assertTrue(PublicId::isValid($model->public_id));
    }

    public function test_keeps_explicit_public_id(): void
    {
        $model = SiteScopedPublicIdModel::query()->create([
            'site_id' => 1,
            'name' => 'Event',
            'public_id' => 'h3k7m2p9',
        ]);

        $this->assertSame('h3k7m2p9', $model->public_id);
    }

    public function test_retries_until_unique_within_site(): void
    {
        SiteScopedPublicIdModel::query()->create([
            'site_id' => 1,
            'name' => 'Existing',
            'public_id' => 'aaaaaaaa',
        ]);

        $calls = 0;

        SiteScopedPublicIdModel::$candidateOverride = function () use (&$calls): string {
            $calls++;

            return $calls === 1 ? 'aaaaaaaa' : 'bbbbbbbb';
        };

        try {
            $model = SiteScopedPublicIdModel::query()->create([
                'site_id' => 1,
                'name' => 'New',
            ]);

            $this->assertSame('bbbbbbbb', $model->public_id);
            $this->assertGreaterThanOrEqual(2, $calls);
        } finally {
            SiteScopedPublicIdModel::$candidateOverride = null;
        }
    }

    public function test_allows_same_public_id_on_different_site(): void
    {
        SiteScopedPublicIdModel::query()->create([
            'site_id' => 1,
            'name' => 'A',
            'public_id' => 'cccccccc',
        ]);

        $model = SiteScopedPublicIdModel::query()->create([
            'site_id' => 2,
            'name' => 'B',
            'public_id' => 'cccccccc',
        ]);

        $this->assertSame('cccccccc', $model->public_id);
    }

    public function test_public_resource_key_helper(): void
    {
        $model = SiteScopedPublicIdModel::query()->create([
            'site_id' => 1,
            'name' => 'Event',
            'public_id' => 'h3k7m2p9',
        ]);

        $this->assertSame('summer-night-h3k7m2p9', $model->publicResourceKey('summer-night'));
    }

    public function test_throws_when_generation_exhausted(): void
    {
        SiteScopedPublicIdModel::query()->create([
            'site_id' => 1,
            'name' => 'Existing',
            'public_id' => 'dddddddd',
        ]);

        SiteScopedPublicIdModel::$candidateOverride = fn (): string => 'dddddddd';
        SiteScopedPublicIdModel::$attemptOverride = 3;

        try {
            $this->expectException(RuntimeException::class);

            SiteScopedPublicIdModel::query()->create([
                'site_id' => 1,
                'name' => 'Clash',
            ]);
        } finally {
            SiteScopedPublicIdModel::$candidateOverride = null;
            SiteScopedPublicIdModel::$attemptOverride = null;
        }
    }
}

class SiteScopedPublicIdModel extends Model
{
    use HasPublicId;

    public $table = 'public_id_models';

    /** @var (callable(): string)|null */
    public static $candidateOverride = null;

    public static ?int $attemptOverride = null;

    public function publicIdUniquenessColumns(): array
    {
        return ['site_id'];
    }

    public function publicIdGenerationAttempts(): int
    {
        return self::$attemptOverride ?? 16;
    }

    protected function newPublicIdCandidate(): string
    {
        if (self::$candidateOverride !== null) {
            return (self::$candidateOverride)();
        }

        return PublicId::generate($this->publicIdLength());
    }
}
