<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleReorderRequest;
use App\Http\Requests\ArticleRequest;
use App\Models\Agent;
use App\Models\Article;
use App\Models\Category;
use App\Services\ArticleImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleImageService $articleImage,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $categoryId = (string) $request->query('category_id', '');
        $keyword = trim((string) $request->query('q', ''));
        $isActive = (string) $request->query('isactive', '');

        $data = Article::query()
            ->with([
                'category:id,name',
                'image',
                'agent:id,name,code',
            ])
            ->when($categoryId !== '', fn ($query) => $query->where('category_id', $categoryId))
            ->when($keyword !== '', fn ($query) => $query->where('name', 'like', "%{$keyword}%"))
            ->when(in_array($isActive, ['Y', 'N'], true), fn ($query) => $query->where('isactive', $isActive))
            ->orderByRaw('seq IS NULL')
            ->orderBy('seq')
            ->orderByDesc('updated')
            ->get();

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
        return view('pages.article.create', [
            'title' => 'เพิ่มบทความ',
            'item' => new Article([
                'isactive' => 'Y',
            ]),
            'categories' => Category::query()->get(['id', 'name']),
            'agents' => $this->agentsForSelect(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ArticleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $article = Article::query()->create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'agent_id' => $validated['agent_id'] ?? null,
            'seq' => (int) Article::query()->max('seq') + 10,
            'isactive' => $validated['isactive'],
            'text' => $validated['text'] ?? null,
            'created' => now(),
            'updated' => now(),
            'createdby' => Auth::id(),
        ]);

        $this->storeCoverImage($request, $article);

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
            ->with([
                'category:id,name',
                'image',
                'agent:id,name,code',
            ])
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
        $item = Article::query()
            ->with('image')
            ->findOrFail($id);

        return view('pages.article.edit', [
            'title' => 'แก้ไขบทความ',
            'item' => $item,
            'categories' => Category::query()->get(['id', 'name']),
            'agents' => $this->agentsForSelect(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ArticleRequest $request, string $id): RedirectResponse
    {
        $validated = $request->validated();

        $item = Article::query()->findOrFail($id);
        $item->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'agent_id' => $validated['agent_id'] ?? null,
            'isactive' => $validated['isactive'],
            'text' => $validated['text'] ?? null,
            'updated' => now(),
        ]);

        $this->replaceCoverImage($request, $item);

        return redirect()
            ->route('article.index')
            ->with('success', 'บันทึกบทความเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $item = Article::query()->findOrFail($id);

        $this->articleImage->deleteLocalCover($item);
        $item->delete();

        return redirect()
            ->route('article.index')
            ->with('success', 'ลบบทความเรียบร้อยแล้ว');
    }

    public function reorder(ArticleReorderRequest $request): JsonResponse
    {
        foreach ($request->validated('order') as $index => $articleId) {
            Article::query()
                ->whereKey($articleId)
                ->update(['seq' => ($index + 1) * 10]);
        }

        return response()->json([
            'message' => 'บันทึกลำดับเรียบร้อยแล้ว',
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Agent>
     */
    private function agentsForSelect()
    {
        return Agent::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    private function storeCoverImage(ArticleRequest $request, Article $article): void
    {
        $file = $this->validatedCoverFile($request);

        if ($file === null) {
            return;
        }

        $this->articleImage->attach($article, $file);
    }

    private function replaceCoverImage(ArticleRequest $request, Article $article): void
    {
        $file = $this->validatedCoverFile($request);

        if ($file === null) {
            return;
        }

        $this->articleImage->replace($article, $file);
    }

    private function validatedCoverFile(ArticleRequest $request): ?UploadedFile
    {
        if (! $request->hasFile('cover')) {
            return null;
        }

        $file = $request->file('cover');

        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            throw ValidationException::withMessages([
                'cover' => 'อัปโหลดรูปหน้าปกไม่สำเร็จ',
            ]);
        }

        return $file;
    }
}
