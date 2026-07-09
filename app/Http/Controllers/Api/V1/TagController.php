<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TagController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $limit = max(1, min(100, $request->integer('limit', 10)));

        $tags = Tag::query()
            ->withCount('assets')
            ->orderByDesc('assets_count')
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'created_at']);

        return TagResource::collection($tags);
    }
}
