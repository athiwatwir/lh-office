<?php

namespace App\View\Components\Workspace;

use App\Models\Agent;
use App\Services\ActiveAgentService;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AgentSelectorModal extends Component
{
    public bool $requiresSelection;

    public function __construct(
        public ?Agent $activeAgent = null,
        ?bool $requiresSelection = null,
    ) {
        $service = app(ActiveAgentService::class);
        $this->activeAgent ??= $service->agent();
        $this->requiresSelection = $requiresSelection ?? ! $service->hasAgent();
    }

    public function render(): View
    {
        return view('components.workspace.agent-selector-modal', [
            'agents' => Agent::query()->orderBy('name')->get(),
        ]);
    }
}
