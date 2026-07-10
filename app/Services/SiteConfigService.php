<?php

namespace App\Services;

use App\Models\Agent;

class SiteConfigService
{
    /**
     * @return array<string, bool>
     */
    public function featuresForAgent(?Agent $agent): array
    {
        return $this->featuresForProfile($this->profileForAgent($agent));
    }

    /**
     * @return array<string, bool>
     */
    public function featuresForAgentId(?string $agentId): array
    {
        if (blank($agentId)) {
            return $this->featuresForProfile($this->defaultProfile());
        }

        $agent = Agent::query()->find($agentId);

        return $this->featuresForAgent($agent);
    }

    public function enabledForAgent(?Agent $agent, string $feature): bool
    {
        return (bool) ($this->featuresForAgent($agent)[$feature] ?? false);
    }

    public function enabledForAgentId(?string $agentId, string $feature): bool
    {
        return (bool) ($this->featuresForAgentId($agentId)[$feature] ?? false);
    }

    public function profileForAgent(?Agent $agent): string
    {
        $profile = strtoupper(trim((string) ($agent?->system_type ?? '')));

        if ($profile !== '' && $this->profileExists($profile)) {
            return $profile;
        }

        return $this->defaultProfile();
    }

    public function profileForAgentId(?string $agentId): string
    {
        if (blank($agentId)) {
            return $this->defaultProfile();
        }

        $agent = Agent::query()->find($agentId);

        return $this->profileForAgent($agent);
    }

    /**
     * @return array<string, bool>
     */
    public function featuresForProfile(string $profile): array
    {
        $profiles = config('site.profiles', []);

        if (! isset($profiles[$profile]) || ! is_array($profiles[$profile])) {
            $profile = $this->defaultProfile();
        }

        return array_map(
            static fn ($value) => (bool) $value,
            $profiles[$profile] ?? [],
        );
    }

    /**
     * @return array{profile: string, features: array<string, bool>}
     */
    public function toArrayForAgent(?Agent $agent): array
    {
        $profile = $this->profileForAgent($agent);

        return [
            'profile' => $profile,
            'features' => $this->featuresForProfile($profile),
        ];
    }

    private function defaultProfile(): string
    {
        $profile = strtoupper(trim((string) config('site.default_profile', 'A')));

        return $this->profileExists($profile) ? $profile : 'A';
    }

    private function profileExists(string $profile): bool
    {
        return isset(config('site.profiles', [])[$profile]);
    }
}
