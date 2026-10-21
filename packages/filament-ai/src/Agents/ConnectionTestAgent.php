<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

/**
 * Minimal agent used only to verify BYOK provider credentials.
 */
#[MaxTokens(16)]
#[Timeout(30)]
class ConnectionTestAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a connection probe. Reply with the single word OK.';
    }
}
