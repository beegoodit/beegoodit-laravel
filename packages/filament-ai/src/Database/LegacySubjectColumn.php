<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

final class LegacySubjectColumn
{
    public static function add(Blueprint $blueprint, bool $unique = false): void
    {
        $blueprint->string('subject_type')->nullable();
        $blueprint->string('subject_id')->nullable();

        if ($unique) {
            $blueprint->unique(['subject_type', 'subject_id']);
        } else {
            $blueprint->index(['subject_type', 'subject_id']);
        }
    }

    /**
     * Copy a legacy tenant/site id into the subject pair, then drop that column.
     * No-op when the table is missing or already has no legacy column.
     */
    public static function convert(string $table, bool $singleNullSubject = false, bool $uniquePair = false): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! Schema::hasColumn($table, 'subject_type') || ! Schema::hasColumn($table, 'subject_id')) {
            Schema::table($table, function (Blueprint $blueprint): void {
                if (! Schema::hasColumn($blueprint->getTable(), 'subject_type')) {
                    $blueprint->string('subject_type')->nullable();
                }

                if (! Schema::hasColumn($blueprint->getTable(), 'subject_id')) {
                    $blueprint->string('subject_id')->nullable();
                }
            });
        }

        $legacy = self::legacyColumn($table);

        if ($legacy !== null) {
            self::copy($table, $legacy);
            self::dropLegacy($table, $legacy);
        }

        if ($uniquePair) {
            self::ensurePairUnique($table);
        } elseif (! self::pairIsIndexed($table)) {
            self::ensurePairIndex($table);
        }

        if ($singleNullSubject) {
            self::ensureSingleNullSubject($table);
        }
    }

    public static function ensureSingleNullSubject(string $table): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (! in_array($driver, ['pgsql', 'sqlite'], true)) {
            return;
        }

        $grammar = Schema::getConnection()->getQueryGrammar();
        $index = $grammar->wrap($table.'_one_null_subject');
        $wrappedTable = $grammar->wrapTable($table);

        DB::statement(
            "CREATE UNIQUE INDEX IF NOT EXISTS {$index} ON {$wrappedTable} ((1)) WHERE subject_type IS NULL AND subject_id IS NULL",
        );
    }

    private static function legacyColumn(string $table): ?string
    {
        $configured = config('filament-ai.tenant_column');
        $candidates = array_values(array_unique(array_filter([
            is_string($configured) && $configured !== '' ? $configured : null,
            'site_id',
            'tenant_id',
        ])));

        foreach ($candidates as $column) {
            if (in_array($column, ['subject_type', 'subject_id'], true)) {
                continue;
            }

            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private static function copy(string $table, string $legacy): void
    {
        $hasValues = DB::table($table)->whereNotNull($legacy)->exists();

        if (! $hasValues) {
            return;
        }

        $model = config('filament-ai.subject_model');

        if (! is_string($model) || $model === '' || ! is_a($model, Model::class, true)) {
            throw new RuntimeException(
                "filament-ai.subject_model must be an Eloquent model class before migrating legacy column [{$legacy}] on [{$table}].",
            );
        }

        $type = (new $model)->getMorphClass();
        $column = DB::connection()->getQueryGrammar()->wrap($legacy);

        DB::table($table)
            ->whereNotNull($legacy)
            ->update([
                'subject_type' => $type,
                'subject_id' => DB::raw('cast('.$column.' as text)'),
            ]);
    }

    private static function dropLegacy(string $table, string $legacy): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($legacy): void {
            $blueprint->dropColumn($legacy);
        });
    }

    private static function ensurePairUnique(string $table): void
    {
        if (self::pairIsIndexed($table)) {
            return;
        }

        $index = 'fai_'.substr(hash('sha256', $table), 0, 16).'_subject';

        Schema::table($table, function (Blueprint $blueprint) use ($index): void {
            $blueprint->unique(['subject_type', 'subject_id'], $index);
        });
    }

    private static function ensurePairIndex(string $table): void
    {
        $index = 'fai_'.substr(hash('sha256', $table), 0, 16).'_idx';

        Schema::table($table, function (Blueprint $blueprint) use ($index): void {
            $blueprint->index(['subject_type', 'subject_id'], $index);
        });
    }

    private static function pairIsIndexed(string $table): bool
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
