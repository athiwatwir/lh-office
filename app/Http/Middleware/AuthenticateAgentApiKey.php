<?php

namespace App\Http\Middleware;

use App\Models\Agent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAgentApiKey
{
    public const REQUEST_ATTRIBUTE = 'api_agent';

    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $this->resolveApiKey($request);

        if ($apiKey === null || $apiKey === '') {
            return response()->json([
                'message' => 'API key is required. Send Authorization: Bearer {api_key} or X-Api-Key header.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $agent = Agent::query()
            ->whereNotNull('api_key')
            ->where('api_key', '!=', '')
            ->where('api_key', $apiKey)
            ->first(['id', 'name', 'code', 'logo']);

        if ($agent === null) {
            return response()->json([
                'message' => 'Invalid API key.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->attributes->set(self::REQUEST_ATTRIBUTE, $agent);

        return $next($request);
    }

    private function resolveApiKey(Request $request): ?string
    {
        $authorization = (string) $request->header('Authorization', '');

        if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches) === 1) {
            return trim($matches[1]);
        }

        $headerKey = $request->header('X-Api-Key');

        return is_string($headerKey) ? trim($headerKey) : null;
    }
}
