<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelectActiveAgentRequest;
use App\Services\ActiveAgentService;
use Illuminate\Http\RedirectResponse;

class ActiveAgentController extends Controller
{
    public function __construct(
        private readonly ActiveAgentService $activeAgent,
    ) {}

    public function store(SelectActiveAgentRequest $request): RedirectResponse
    {
        $agent = $this->activeAgent->set($request->validated('agent_id'));

        return redirect()
            ->intended(route('dashboard'))
            ->with('success', 'เลือกเอเจนต์ '.$agent->name.' เรียบร้อยแล้ว');
    }

    public function destroy(): RedirectResponse
    {
        $this->activeAgent->clear();

        return redirect()
            ->back()
            ->with('success', 'กรุณาเลือกเอเจนต์เพื่อใช้งานระบบ');
    }
}
