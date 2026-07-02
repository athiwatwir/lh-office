<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagBulkStoreRequest;
use App\Http\Requests\TagRequest;
use App\Models\Tag;
use App\Services\TagImportFromZonesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        $data = Tag::query()
            ->withCount('assets')
            ->orderBy('name')
            ->paginate(20);

        return view('pages.tag.index', [
            'title' => 'ทำเล/กลุ่ม (Tags)',
            'data' => $data,
        ]);
    }

    public function create(): View
    {
        return view('pages.tag.create', [
            'title' => 'เพิ่มแท็ก',
            'item' => new Tag(),
        ]);
    }

    public function store(TagRequest $request): RedirectResponse
    {
        Tag::query()->create($request->validated());

        return redirect()
            ->route('tag.index')
            ->with('success', 'เพิ่มแท็กเรียบร้อยแล้ว');
    }

    public function bulkStore(TagBulkStoreRequest $request): RedirectResponse
    {
        $names = $request->parsedNames();

        if ($names === []) {
            return redirect()
                ->route('tag.index')
                ->with('error', 'ไม่พบชื่อแท็กที่จะเพิ่ม');
        }

        $existing = Tag::query()
            ->whereIn('name', $names)
            ->pluck('name')
            ->all();

        $existingLookup = array_fill_keys($existing, true);
        $created = 0;

        foreach ($names as $name) {
            if (isset($existingLookup[$name])) {
                continue;
            }

            Tag::query()->create(['name' => $name]);
            $existingLookup[$name] = true;
            $created++;
        }

        $skipped = count($names) - $created;

        if ($created === 0) {
            return redirect()
                ->route('tag.index')
                ->with('error', 'แท็กทั้งหมดมีในระบบแล้ว ('.number_format($skipped).' รายการ)');
        }

        $message = 'เพิ่มแท็ก '.number_format($created).' รายการเรียบร้อยแล้ว';

        if ($skipped > 0) {
            $message .= ' (ข้าม '.number_format($skipped).' รายการที่ซ้ำ)';
        }

        return redirect()
            ->route('tag.index')
            ->with('success', $message);
    }

    public function importFromZones(TagImportFromZonesService $import): RedirectResponse
    {
        $result = $import->import();

        if ($result['total'] === 0) {
            return redirect()
                ->route('tag.index')
                ->with('error', 'ไม่พบรายชื่อใน description ของโซน');
        }

        if ($result['created'] === 0) {
            return redirect()
                ->route('tag.index')
                ->with('error', 'แท็กจากโซนทั้งหมดมีในระบบแล้ว ('.number_format($result['skipped']).' รายการ)');
        }

        $message = 'นำเข้าแท็กจากโซน '.number_format($result['created']).' รายการเรียบร้อยแล้ว';

        if ($result['skipped'] > 0) {
            $message .= ' (ข้าม '.number_format($result['skipped']).' รายการที่ซ้ำ)';
        }

        return redirect()
            ->route('tag.index')
            ->with('success', $message);
    }

    public function edit(string $tag): View
    {
        $item = Tag::query()
            ->withCount('assets')
            ->findOrFail($tag);

        return view('pages.tag.edit', [
            'title' => 'แก้ไขแท็ก',
            'item' => $item,
        ]);
    }

    public function update(TagRequest $request, string $tag): RedirectResponse
    {
        $item = Tag::query()->findOrFail($tag);

        $item->update($request->validated());

        return redirect()
            ->route('tag.index')
            ->with('success', 'บันทึกแท็กเรียบร้อยแล้ว');
    }

    public function destroy(string $tag): RedirectResponse
    {
        $item = Tag::query()
            ->withCount('assets')
            ->findOrFail($tag);

        if ($item->assets_count > 0) {
            return redirect()
                ->route('tag.index')
                ->with('error', 'ไม่สามารถลบได้ เนื่องจากมีทรัพย์สินที่ใช้แท็กนี้อยู่');
        }

        $item->delete();

        return redirect()
            ->route('tag.index')
            ->with('success', 'ลบแท็กเรียบร้อยแล้ว');
    }
}
