<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Agents;

use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Models\Setting;
use BeegoodIT\FilamentAi\Services\ToolContext;
use BeegoodIT\FilamentAi\Support\ChatMode;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Host-agnostic Filament AI shell agent (not TallCMS ContentAgent).
 */
class ShellAgent implements Agent, Conversational, HasTools
{
    use Promptable;
    use RemembersConversations;

    public function __construct(
        public ToolContext $context,
        public Setting $settings,
        public Authenticatable $user,
    ) {}

    public function instructions(): Stringable|string
    {
        $base = <<<'INSTRUCTIONS'
You are a helpful AI assistant inside a Filament admin panel.

Rules:
- Only use tools that appear in your tool list; missing tools mean the host disabled that capability or the current chat mode forbids them.
- Prefer concise answers. Ask clarifying questions when requirements are ambiguous.
- When you change something via tools, summarize what you changed.
INSTRUCTIONS;

        $parts = [$base, $this->modeInstructions()];

        foreach (app(CapabilityRegistry::class)->instructionFragments($this->settings) as $fragment) {
            $parts[] = $fragment;
        }

        $workspaceContext = $this->settings->customInstructionsFragment();

        if ($workspaceContext !== null) {
            $parts[] = $workspaceContext;
        }

        return implode("\n\n", $parts);
    }

    public function tools(): iterable
    {
        return app(CapabilityRegistry::class)->toolsFor(
            $this->settings,
            $this->context->mode,
        );
    }

    protected function modeInstructions(): string
    {
        return match ($this->context->mode) {
            ChatMode::Ask => <<<'MODE'
Current mode: Ask (read-only).
- Answer questions using read tools when available.
- Do not create, update, or delete records.
- If the user asks for changes, explain what you would do and suggest switching to Plan or Agent mode.
MODE,
            ChatMode::Plan => <<<'MODE'
Current mode: Plan (read-only).
- Research with read tools when available, then produce a markdown plan — do not execute writes.
- Keep steps concrete so the owner can switch to Agent mode and execute them.
- Do not claim that records were created or updated.
MODE,
            ChatMode::Agent => <<<'MODE'
Current mode: Agent.
- You may use available write tools subject to capability settings.
- Only call tools present in your tool list.
MODE,
        };
    }
}
