<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Models\McpToken;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

final class UuidUserMcpTokenTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('filament-ai.user_key_type', 'uuid');
        $app['config']->set('auth.providers.users.model', UuidUser::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('users');
        Schema::dropIfExists((string) config('filament-ai.mcp.tokens_table', 'beegoodit_filament_ai_mcp_tokens'));

        Schema::create('users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        $mcpTokensMigration = require __DIR__.'/../database/migrations/2026_10_13_000003_create_filament_ai_mcp_tokens_table.php';
        $mcpTokensMigration->up();
    }

    #[Test]
    public function it_stores_uuid_created_by_user_id_and_resolves_relation(): void
    {
        $user = UuidUser::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'UUID User',
            'email' => 'uuid@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($user);

        $issued = McpToken::issue(
            label: 'cursor',
            tenantId: 1,
            createdByUserId: $user->getAuthIdentifier(),
            scope: McpToken::SCOPE_WRITE,
        );

        $this->assertSame($user->id, $issued['token']->created_by_user_id);
        $this->assertTrue($issued['token']->createdBy->is($user));
    }
}

class UuidUser extends Authenticatable
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'users';

    protected $guarded = [];
}
