<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Services\PropertyApiImageWarmer;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SellerApiController extends Controller
{
    public function __construct(
        private readonly PropertyApiImageWarmer $imageWarmer,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $agents = User::query()
            ->with('image')
            ->orderBy('seq')
            ->orderBy('firstname')
            ->where('isseller', 'Y')
            ->get();

        $this->imageWarmer->warmUserProfileImages($agents);

        return UserResource::collection($agents);
    }
}
