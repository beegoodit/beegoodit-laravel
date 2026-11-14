<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests\Support;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class StubWriteTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Write stub';
    }

    public function handle(Request $request): Stringable|string
    {
        return 'written';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
