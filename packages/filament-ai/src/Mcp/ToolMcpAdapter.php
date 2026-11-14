<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Mcp;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool as AiTool;
use Laravel\Ai\Tools\Request as AiToolRequest;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

final class ToolMcpAdapter extends Tool
{
    public function __construct(
        private readonly AiTool $aiTool,
        private readonly bool $readOnly = true,
    ) {
        $this->name = Str::kebab(class_basename($this->aiTool::class));
        $this->title = Str::headline(class_basename($this->aiTool::class));
        $this->description = (string) $this->aiTool->description();
    }

    public function handle(Request $request): Response
    {
        $result = $this->aiTool->handle(new AiToolRequest($request->all()));

        return Response::text((string) $result);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return $this->aiTool->schema($schema);
    }

    /**
     * @return array<string, mixed>
     */
    public function annotations(): array
    {
        return $this->readOnly
            ? ['readOnlyHint' => true]
            : [];
    }
}
