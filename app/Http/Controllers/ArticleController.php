<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $categoryId = (string) $request->query('category_id', '');
        $keyword = trim((string) $request->query('q', ''));
        $isActive = (string) $request->query('isactive', '');

        $data = Article::query()
            ->with('category:id,name')
            ->when($categoryId !== '', fn ($query) => $query->where('category_id', $categoryId))
            ->when($keyword !== '', fn ($query) => $query->where('name', 'like', "%{$keyword}%"))
            ->when(in_array($isActive, ['Y', 'N'], true), fn ($query) => $query->where('isactive', $isActive))
            ->orderByDesc('created')
            ->orderByDesc('updated')
            ->paginate(20)
            ->withQueryString();

        return view('pages.article.index', [
            'title' => 'รายการบทความ',
            'data' => $data,
            'categories' => Category::query()->get(['id', 'name']),
            'filters' => [
                'category_id' => $categoryId,
                'q' => $keyword,
                'isactive' => $isActive,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $nextSeq = (int) Article::query()->max('seq') + 10;

        return view('pages.article.create', [
            'title' => 'เพิ่มบทความ',
            'item' => new Article([
                'seq' => $nextSeq,
                'isactive' => 'Y',
            ]),
            'categories' => Category::query()->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'seq' => ['required', 'integer', 'min:0'],
            'isactive' => ['required', 'in:Y,N'],
            'text' => ['nullable', 'string'],
        ]);

        Article::query()->create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'seq' => $validated['seq'],
            'isactive' => $validated['isactive'],
            'text' => $validated['text'] ?? null,
            'created' => now(),
            'updated' => now(),
            'createdby' => Auth::id(),
        ]);

        return redirect()
            ->route('article.index')
            ->with('success', 'เพิ่มบทความเรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $item = Article::query()
            ->with('category:id,name')
            ->findOrFail($id);

        return view('pages.article.show', [
            'title' => 'รายละเอียดบทความ',
            'item' => $item,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $item = Article::query()->findOrFail($id);

        return view('pages.article.edit', [
            'title' => 'แก้ไขบทความ',
            'item' => $item,
            'categories' => Category::query()->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'seq' => ['required', 'integer', 'min:0'],
            'isactive' => ['required', 'in:Y,N'],
            'text' => ['nullable', 'string'],
        ]);

        $item = Article::query()->findOrFail($id);
        $item->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'seq' => $validated['seq'],
            'isactive' => $validated['isactive'],
            'text' => $validated['text'] ?? null,
            'updated' => now(),
        ]);

        return redirect()
            ->route('article.index')
            ->with('success', 'บันทึกบทความเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        Article::query()
            ->whereKey($id)
            ->delete();

        return redirect()
            ->route('article.index')
            ->with('success', 'ลบบทความเรียบร้อยแล้ว');
    }
}
