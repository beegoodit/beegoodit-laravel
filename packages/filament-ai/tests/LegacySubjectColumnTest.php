<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Database\LegacySubjectColumn;
use BeegoodIT\FilamentAi\Tests\Fixtures\SubjectOwner;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

class LegacySubjectColumnTest extends TestCase
{
    #[Test]
    public function it_copies_legacy_tenant_ids_and_drops_the_column(): void
    {
        Schema::dropIfExists('filament_ai_legacy_settings');

        Schema::create('filament_ai_legacy_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->boolean('enabled')->default(false);
        });

        DB::table('filament_ai_legacy_settings')->insert([
            ['id' => 1, 'tenant_id' => 12, 'enabled' => true],
            ['id' => 2, 'tenant_id' => null, 'enabled' => false],
        ]);

        config([
            'filament-ai.tenant_column' => 'tenant_id',
            'filament-ai.subject_model' => SubjectOwner::class,
        ]);

        LegacySubjectColumn::convert('filament_ai_legacy_settings', singleNullSubject: true);

        $this->assertFalse(Schema::hasColumn('filament_ai_legacy_settings', 'tenant_id'));
        $this->assertTrue(Schema::hasColumn('filament_ai_legacy_settings', 'subject_type'));
        $this->assertTrue($this->subjectPairIsIndexed('filament_ai_legacy_settings'));

        $copied = DB::table('filament_ai_legacy_settings')->where('id', 1)->first();
        $empty = DB::table('filament_ai_legacy_settings')->where('id', 2)->first();

        $this->assertSame((new SubjectOwner)->getMorphClass(), $copied->subject_type);
        $this->assertSame('12', $copied->subject_id);
        $this->assertNull($empty->subject_type);
        $this->assertNull($empty->subject_id);
    }

    #[Test]
    public function it_drops_a_unique_legacy_site_id_like_tallcms_settings(): void
    {
        Schema::dropIfExists('filament_ai_legacy_unique_settings');

        Schema::create('filament_ai_legacy_unique_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->unique();
            $table->index('site_id');
            $table->boolean('enabled')->default(false);
        });

        DB::table('filament_ai_legacy_unique_settings')->insert([
            ['id' => 1, 'site_id' => 7, 'enabled' => true],
            ['id' => 2, 'site_id' => null, 'enabled' => false],
        ]);

        config([
            'filament-ai.tenant_column' => 'site_id',
            'filament-ai.subject_model' => SubjectOwner::class,
        ]);

        LegacySubjectColumn::convert('filament_ai_legacy_unique_settings', singleNullSubject: true, uniquePair: true);

        $this->assertFalse(Schema::hasColumn('filament_ai_legacy_unique_settings', 'site_id'));
        $this->assertTrue(Schema::hasColumn('filament_ai_legacy_unique_settings', 'subject_type'));
        $this->assertTrue($this->subjectPairIsIndexed('filament_ai_legacy_unique_settings'));

        $copied = DB::table('filament_ai_legacy_unique_settings')->where('id', 1)->first();

        $this->assertSame((new SubjectOwner)->getMorphClass(), $copied->subject_type);
        $this->assertSame('7', $copied->subject_id);
    }

    #[Test]
    public function it_refuses_to_copy_ids_without_a_subject_model(): void
    {
        Schema::dropIfExists('filament_ai_legacy_tokens');

        Schema::create('filament_ai_legacy_tokens', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
        });

        DB::table('filament_ai_legacy_tokens')->insert([
            'id' => 1,
            'site_id' => 3,
        ]);

        config([
            'filament-ai.tenant_column' => 'site_id',
            'filament-ai.subject_model' => null,
        ]);

        $this->expectException(\RuntimeException::class);

        LegacySubjectColumn::convert('filament_ai_legacy_tokens');
    }

    private function subjectPairIsIndexed(string $table): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            $columns = $index['columns'] ?? [];

            if ($columns === ['subject_type', 'subject_id'] || $columns === ['subject_id', 'subject_type']) {
                return true;
            }
        }

        return false;
    }
}
