<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $data = Zone::query()
            ->withCount(['assets', 'customer_assets'])
            ->orderBy('name')
            ->paginate(20);

        return view('pages.zone.index', [
            'title' => 'โซน',
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.zone.create', [
            'title' => 'เพิ่มโซน',
            'item' => new Zone(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        Zone::query()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'created' => now(),
        ]);

        return redirect()
            ->route('zone.index')
            ->with('success', 'เพิ่มโซนเรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $item = Zone::query()
            ->withCount(['assets', 'customer_assets'])
            ->findOrFail($id);

        return view('pages.zone.edit', [
            'title' => 'แก้ไขโซน',
            'item' => $item,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $item = Zone::query()->findOrFail($id);

        $item->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('zone.index')
            ->with('success', 'บันทึกโซนเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $item = Zone::query()
            ->withCount(['assets', 'customer_assets'])
            ->findOrFail($id);

        if (($item->assets_count + $item->customer_assets_count) > 0) {
            return redirect()
                ->route('zone.index')
                ->with('error', 'ไม่สามารถลบได้ เนื่องจากมีข้อมูลที่ใช้งานโซนนี้อยู่');
        }

        Zone::query()
            ->whereKey($item->id)
            ->delete();

        return redirect()
            ->route('zone.index')
            ->with('success', 'ลบโซนเรียบร้อยแล้ว');
    }
}
