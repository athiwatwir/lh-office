<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AgentResource;
use App\Models\Agent;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AgentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $agents = Agent::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'logo']);

        return AgentResource::collection($agents);
    }
}
