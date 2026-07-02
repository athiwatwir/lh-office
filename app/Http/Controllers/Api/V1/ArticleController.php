<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthenticateAgentApiKey;
use App\Http\Requests\Api\ArticleIndexRequest;
use App\Http\Resources\Api\ArticleDetailResource;
use App\Http\Resources\Api\ArticleListResource;
use App\Http\Resources\Api\CategoryResource;
use App\Models\Agent;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    public function index(ArticleIndexRequest $request): AnonymousResourceCollection
    {
        /** @var Agent $agent */
        $agent = $request->attributes->get(AuthenticateAgentApiKey::REQUEST_ATTRIBUTE);

        $articles = Article::query()
            ->active()
            ->visibleToAgent($agent->id)
            ->with([
                'category:id,name',
                'image',
            ])
            ->when(
                $request->categoryId(),
                fn ($query) => $query->where('category_id', $request->categoryId()),
            )
            ->when(
                $request->keyword(),
                fn ($query, string $keyword) => $query->where('name', 'like', "%{$keyword}%"),
            )
            ->orderedForDisplay()
            ->paginate($request->perPage());

        $categories = Category::query()
            ->where('isactive', 'Y')
            ->when(
                $request->categoryId(),
                fn ($query) => $query->where('id', $request->categoryId()),
            )
            ->orderByRaw('seq IS NULL')
            ->orderBy('seq')
            ->orderBy('name')
            ->get(['id', 'name', 'seq']);

        return ArticleListResource::collection($articles)->additional([
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    public function show(Request $request, string $article): ArticleDetailResource
    {
        /** @var Agent $agent */
        $agent = $request->attributes->get(AuthenticateAgentApiKey::REQUEST_ATTRIBUTE);

        $item = Article::query()
            ->active()
            ->visibleToAgent($agent->id)
            ->with([
                'category:id,name',
                'image',
                'agent:id,name,code',
            ])
            ->findOrFail($article);

        return new ArticleDetailResource($item);
    }
}
