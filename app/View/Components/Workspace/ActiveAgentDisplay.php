<?php

namespace App\View\Components\Workspace;

use App\Models\Agent;
use App\Services\ActiveAgentService;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ActiveAgentDisplay extends Component
{
    public function __construct(
        public ?Agent $activeAgent = null,
    ) {
        $this->activeAgent ??= app(ActiveAgentService::class)->agent();
    }

    public function render(): View
    {
        return view('components.workspace.active-agent-display');
    }
}
