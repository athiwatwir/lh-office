<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgentRequest;
use App\Models\Agent;
use App\Services\ImageUploadOptions;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AgentController extends Controller
{
    private const LOGO_DIRECTORY = Agent::LOGO_DIRECTORY;

    public function __construct(
        private readonly ImageUploadService $imageUpload,
    ) {}

    public function index(): View
    {
        $data = Agent::query()
            ->withCount('assets')
            ->orderBy('name')
            ->paginate(20);

        return view('pages.agent.index', [
            'title' => 'รายชื่อเอเจนต์',
            'data' => $data,
        ]);
    }

    public function create(): View
    {
        return view('pages.agent.create', [
            'title' => 'เพิ่มเอเจนต์',
            'item' => new Agent,
        ]);
    }

    public function store(AgentRequest $request): RedirectResponse
    {
        Agent::query()->create([
            'name' => $request->validated('name'),
            'code' => $request->validated('code'),
            'api_key' => $request->validated('api_key'),
            'logo' => $this->resolveUploadedLogo($request),
        ]);

        return redirect()
            ->route('agent.index')
            ->with('success', 'เพิ่มเอเจนต์เรียบร้อยแล้ว');
    }

    public function edit(string $agent): View
    {
        $item = Agent::query()
            ->withCount('assets')
            ->findOrFail($agent);

        return view('pages.agent.edit', [
            'title' => 'แก้ไขเอเจนต์',
            'item' => $item,
        ]);
    }

    public function update(AgentRequest $request, string $agent): RedirectResponse
    {
        $item = Agent::query()->findOrFail($agent);

        $data = [
            'name' => $request->validated('name'),
            'code' => $request->validated('code'),
            'api_key' => $request->validated('api_key'),
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->imageUpload->replace(
                $item->logo,
                $request->file('logo'),
                $this->logoUploadOptions(),
            );
        }

        $item->update($data);

        return redirect()
            ->route('agent.index')
            ->with('success', 'บันทึกเอเจนต์เรียบร้อยแล้ว');
    }

    public function destroy(string $agent): RedirectResponse
    {
        $item = Agent::query()->findOrFail($agent);

        if ($item->isInUse()) {
            return redirect()
                ->route('agent.index')
                ->with('error', 'ไม่สามารถลบได้ เนื่องจากมีทรัพย์สินที่ใช้งานเอเจนต์นี้อยู่');
        }

        $this->imageUpload->delete($item->logo, self::LOGO_DIRECTORY);
        $item->delete();

        return redirect()
            ->route('agent.index')
            ->with('success', 'ลบเอเจนต์เรียบร้อยแล้ว');
    }

    private function resolveUploadedLogo(AgentRequest $request): ?string
    {
        if (! $request->hasFile('logo')) {
            return null;
        }

        return $this->imageUpload->store(
            $request->file('logo'),
            $this->logoUploadOptions(),
        );
    }

    private function logoUploadOptions(): ImageUploadOptions
    {
        return new ImageUploadOptions(
            directory: self::LOGO_DIRECTORY,
            quality: 85,
            maxWidth: 400,
            maxHeight: 400,
        );
    }
}
