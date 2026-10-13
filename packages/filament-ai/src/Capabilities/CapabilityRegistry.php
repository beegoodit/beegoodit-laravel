<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Capabilities;

use BeegoodIT\FilamentAi\Capabilities\Contracts\CapabilityProvider;
use BeegoodIT\FilamentAi\Capabilities\Exceptions\DuplicateToolNameException;
use BeegoodIT\FilamentAi\Models\Setting;
use BeegoodIT\FilamentAi\Support\ChatMode;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\ToolNameResolver;

class CapabilityRegistry
{
    public const SUPPORTED_CONTRACT_VERSION = 1;

    /** @var array<string, CapabilityProvider> */
    private array $providers = [];

    public function register(CapabilityProvider $provider): void
    {
        if ($provider->contractVersion() !== self::SUPPORTED_CONTRACT_VERSION) {
            Log::warning('Skipping AI capability provider with unsupported contract version.', [
                'provider' => $provider->id(),
                'version' => $provider->contractVersion(),
                'supported' => self::SUPPORTED_CONTRACT_VERSION,
            ]);

            return;
        }

        $this->providers[$provider->id()] = $provider;
        $this->assertUniqueToolNames();
    }

    /**
     * @return list<CapabilityProvider>
     */
    public function providers(): array
    {
        return array_values($this->providers);
    }

    public function provider(string $id): ?CapabilityProvider
    {
        return $this->providers[$id] ?? null;
    }

    /**
     * Nested defaults for all registered providers.
     *
     * @return array<string, array<string, bool>>
     */
    public function defaults(): array
    {
        $defaults = [];

        foreach ($this->providers as $provider) {
            $defaults[$provider->id()] = [];
            foreach ($provider->capabilities() as $capability) {
                $defaults[$provider->id()][$capability->key] = $capability->default;
            }
        }

        return $defaults;
    }

    /**
     * Merge stored capabilities with registered provider defaults.
     * Unknown provider ids in storage are preserved.
     *
     * @param  array<string, mixed>|null  $stored
     * @return array<string, array<string, bool>|mixed>
     */
    public function merge(?array $stored): array
    {
        $merged = $this->defaults();

        if ($stored === null) {
            return $merged;
        }

        $normalized = $this->normalizeStored($stored);

        foreach ($normalized as $providerId => $groups) {
            if (! is_array($groups)) {
                continue;
            }

            if (! isset($merged[$providerId]) || ! is_array($merged[$providerId])) {
                $merged[$providerId] = [];
                foreach ($groups as $key => $value) {
                    if (is_string($key)) {
                        $merged[$providerId][$key] = (bool) $value;
                    }
                }

                continue;
            }

            foreach ($groups as $key => $value) {
                if (is_string($key)) {
                    $merged[$providerId][$key] = (bool) $value;
                }
            }
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $stored
     * @return array<string, array<string, mixed>>
     */
    public function normalizeStored(array $stored): array
    {
        if ($this->looksNested($stored)) {
            /** @var array<string, array<string, mixed>> $stored */
            return $stored;
        }

        // Flat bool map with a single registered provider → nest under that provider.
        if (count($this->providers) === 1 && $this->looksFlat($stored)) {
            $providerId = array_key_first($this->providers);

            return [$providerId => $stored];
        }

        return $stored;
    }

    public function can(array $resolved, string $providerId, string $capabilityKey): bool
    {
        $group = $resolved[$providerId] ?? null;

        if (! is_array($group)) {
            return false;
        }

        return (bool) ($group[$capabilityKey] ?? false);
    }

    /**
     * @param  ChatMode|\BackedEnum|string  $mode  Hosts may pass their own backed enum (same values).
     * @return list<object>
     */
    public function toolsFor(Setting $settings, ChatMode|\BackedEnum|string $mode): array
    {
        $mode = $this->normalizeMode($mode);
        $resolved = $settings->resolvedCapabilities();
        $tools = [];
        $seenClasses = [];
        $seenNames = [];

        foreach ($this->providers as $provider) {
            foreach ($provider->alwaysOnTools() as $class) {
                if (isset($seenClasses[$class])) {
                    continue;
                }
                $seenClasses[$class] = true;
                $tool = app($class);
                $seenNames[$this->toolName($tool)] = true;
                $tools[] = $tool;
            }

            foreach ($provider->capabilities() as $capability) {
                if (! $this->can($resolved, $provider->id(), $capability->key)) {
                    continue;
                }

                if ($mode->isReadOnly() && ! $capability->allowedInReadOnlyMode) {
                    continue;
                }

                $requirementsMet = true;
                foreach ($capability->requires as $requiredKey) {
                    if (! $this->can($resolved, $provider->id(), $requiredKey)) {
                        $requirementsMet = false;
                        break;
                    }
                }

                if (! $requirementsMet) {
                    continue;
                }

                foreach ($capability->tools as $class) {
                    if (isset($seenClasses[$class])) {
                        continue;
                    }
                    $seenClasses[$class] = true;
                    $tool = app($class);
                    $seenNames[$this->toolName($tool)] = true;
                    $tools[] = $tool;
                }

                foreach ($provider->providerNativeTools($capability->key, $settings) as $native) {
                    $name = $this->toolName($native);

                    if (isset($seenNames[$name])) {
                        continue;
                    }

                    $seenNames[$name] = true;
                    $tools[] = $native;
                }
            }
        }

        return $tools;
    }

    /**
     * @return list<string>
     */
    public function instructionFragments(Setting $settings): array
    {
        $resolved = $settings->resolvedCapabilities();
        $fragments = [];

        foreach ($this->providers as $provider) {
            $anyEnabled = false;
            foreach ($provider->capabilities() as $capability) {
                if ($this->can($resolved, $provider->id(), $capability->key)) {
                    $anyEnabled = true;
                    break;
                }
            }

            if (! $anyEnabled) {
                continue;
            }

            $fragment = $provider->instructions($settings);
            if (filled($fragment)) {
                $fragments[] = $fragment;
            }
        }

        return $fragments;
    }

    private function normalizeMode(ChatMode|\BackedEnum|string $mode): ChatMode
    {
        if ($mode instanceof ChatMode) {
            return $mode;
        }

        $value = $mode instanceof \BackedEnum ? (string) $mode->value : (string) $mode;

        return ChatMode::tryFrom($value) ?? ChatMode::Ask;
    }

    private function toolName(object $tool): string
    {
        if ($tool instanceof Tool) {
            return ToolNameResolver::resolve($tool);
        }

        return is_callable([$tool, 'name']) ? (string) $tool->name() : class_basename($tool);
    }

    private function looksNested(array $stored): bool
    {
        foreach ($stored as $value) {
            if (is_array($value)) {
                return true;
            }
        }

        return false;
    }

    private function looksFlat(array $stored): bool
    {
        if ($stored === []) {
            return false;
        }

        foreach ($stored as $key => $value) {
            if (! is_string($key) || is_array($value)) {
                return false;
            }
        }

        return true;
    }

    private function assertUniqueToolNames(): void
    {
        $names = [];

        foreach ($this->providers as $provider) {
            $classes = $provider->alwaysOnTools();
            foreach ($provider->capabilities() as $capability) {
                foreach ($capability->tools as $class) {
                    $classes[] = $class;
                }
            }

            foreach (array_unique($classes) as $class) {
                $name = class_basename($class);

                if (isset($names[$name]) && $names[$name] !== $class) {
                    throw new DuplicateToolNameException(
                        "Duplicate AI tool name [{$name}] from [{$names[$name]}] and [{$class}] (providers registered)."
                    );
                }

                $names[$name] = $class;
            }
        }
    }
}
