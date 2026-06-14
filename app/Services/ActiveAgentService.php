<?php

namespace App\Services;

use App\Models\Agent;

class ActiveAgentService
{
    public const SESSION_KEY = 'active_agent_id';

    public function id(): ?string
    {
        $id = session(self::SESSION_KEY);

        return filled($id) ? (string) $id : null;
    }

    public function agent(): ?Agent
    {
        $id = $this->id();

        if (blank($id)) {
            return null;
        }

        $agent = Agent::query()->find($id);

        if ($agent === null) {
            $this->clear();

            return null;
        }

        return $agent;
    }

    public function hasAgent(): bool
    {
        return $this->agent() !== null;
    }

    public function set(string $agentId): Agent
    {
        $agent = Agent::query()->findOrFail($agentId);

        session([self::SESSION_KEY => $agent->id]);

        return $agent;
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
