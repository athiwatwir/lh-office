<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthenticateAgentApiKey;
use App\Http\Resources\Api\SiteConfigResource;
use App\Models\Agent;
use App\Services\SiteConfigService;
use Illuminate\Http\Request;

class SiteConfigController extends Controller
{
    public function __construct(
        private readonly SiteConfigService $siteConfig,
    ) {}

    public function show(Request $request): SiteConfigResource
    {
        /** @var Agent $agent */
        $agent = $request->attributes->get(AuthenticateAgentApiKey::REQUEST_ATTRIBUTE);

        return new SiteConfigResource($this->siteConfig->toArrayForAgent($agent));
    }
}
