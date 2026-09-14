<?php

namespace BeegoodIT\FilamentStreaks\Support;

use BeegoodIT\FilamentStreaks\Contracts\StrategyContract;
use InvalidArgumentException;

class StrategyResolver
{
    /**
     * @return array<string, mixed>
     */
    public function config(string $strategyIdentifier): array
    {
        $config = config("filament-streaks.strategies.{$strategyIdentifier}");

        if (! is_array($config)) {
            throw new InvalidArgumentException("Unknown streak strategy [{$strategyIdentifier}].");
        }

        return $config;
    }

    public function resolve(string $strategyIdentifier): StrategyContract
    {
        $config = $this->config($strategyIdentifier);
        $class = $config['strategy'] ?? null;

        if (! is_string($class) || ! class_exists($class)) {
            throw new InvalidArgumentException("Strategy class not configured for [{$strategyIdentifier}].");
        }

        $strategy = app($class);

        if (! $strategy instanceof StrategyContract) {
            throw new InvalidArgumentException("Strategy [{$class}] must implement StrategyContract.");
        }

        return $strategy;
    }
}
