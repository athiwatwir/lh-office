<?php

namespace App\Services;

use App\Models\Asset;

class PropertyProfileTransferService
{
    public function __construct(
        private readonly SiteConfigService $siteConfig,
    ) {}

    /**
     * Adapt price fields when moving/copying between agents with different system profiles.
     *
     * @return array<string, mixed>
     */
    public function adaptPricesForAgentTransition(Asset $asset, ?string $sourceAgentId, string $targetAgentId): array
    {
        $sourceProfile = $this->siteConfig->profileForAgentId($sourceAgentId);
        $targetProfile = $this->siteConfig->profileForAgentId($targetAgentId);

        if ($sourceProfile === $targetProfile) {
            return [];
        }

        if ($sourceProfile === 'A' && $targetProfile === 'B') {
            return [
                'price_special' => $asset->price_amounnt,
                'price_amounnt' => 0,
                'isspecial_marketprice' => 'Y',
            ];
        }

        if ($sourceProfile === 'B' && $targetProfile === 'A') {
            return [
                'price_amounnt' => $asset->price_special ?? $asset->price_amounnt,
                'price_special' => null,
                'isspecial_marketprice' => 'N',
            ];
        }

        return [];
    }
}
