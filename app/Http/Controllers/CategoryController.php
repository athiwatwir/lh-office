<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $data = Category::query()
            ->withCount('articles')
            ->orderBy('seq')
            ->orderBy('name')
            ->paginate(20);

        return view('pages.category.index', [
            'title' => 'ประเภทของบทความ',
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $nextSeq = (int) Category::query()->max('seq') + 10;

        return view('pages.category.create', [
            'title' => 'เพิ่มประเภทบทความ',
            'item' => new Category([
                'seq' => $nextSeq,
                'isactive' => 'Y',
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'decription' => ['nullable', 'string', 'max:3000'],
            'seq' => ['required', 'integer', 'min:0'],
            'isactive' => ['required', 'in:Y,N'],
        ]);

        Category::query()->create([
            'name' => $validated['name'],
            'decription' => $validated['decription'] ?? null,
            'seq' => $validated['seq'],
            'isactive' => $validated['isactive'],
            'created' => now(),
            'createdby' => Auth::id(),
        ]);

        return redirect()
            ->route('category.index')
            ->with('success', 'เพิ่มประเภทบทความเรียบร้อยแล้ว');
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
        $item = Category::query()
            ->withCount('articles')
            ->findOrFail($id);

        return view('pages.category.edit', [
            'title' => 'แก้ไขประเภทบทความ',
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
            'decription' => ['nullable', 'string', 'max:3000'],
            'seq' => ['required', 'integer', 'min:0'],
            'isactive' => ['required', 'in:Y,N'],
        ]);

        $item = Category::query()->findOrFail($id);

        $item->update([
            'name' => $validated['name'],
            'decription' => $validated['decription'] ?? null,
            'seq' => $validated['seq'],
            'isactive' => $validated['isactive'],
        ]);

        return redirect()
            ->route('category.index')
            ->with('success', 'บันทึกประเภทบทความเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $item = Category::query()
            ->withCount('articles')
            ->findOrFail($id);

        if ($item->articles_count > 0) {
            return redirect()
                ->route('category.index')
                ->with('error', 'ไม่สามารถลบได้ เนื่องจากมีบทความที่ใช้งานประเภทนี้อยู่');
        }

        Category::query()
            ->whereKey($item->id)
            ->delete();

        return redirect()
            ->route('category.index')
            ->with('success', 'ลบประเภทบทความเรียบร้อยแล้ว');
    }
}
