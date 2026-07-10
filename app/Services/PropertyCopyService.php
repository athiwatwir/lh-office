<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Asset;
use App\Models\AssetImage;
use App\Models\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class PropertyCopyService
{
    /**
     * @var list<string>
     */
    private const YN_FIELDS = [
        'isspecial_marketprice',
        'isspecial_appraised',
        'iscovering',
        'isdweller',
        'issale',
        'isrent',
        'issalerent',
        'issellout',
        'issaledown',
        'isactive',
    ];

    public function __construct(
        private readonly ImageProxyService $imageProxy,
        private readonly PropertyProfileTransferService $profileTransfer,
    ) {}

    public function copyToAgent(Asset $source, string $targetAgentId, string $targetAssetTypeId, ?string $createdBy = null): Asset
    {
        if ($source->agent_id === $targetAgentId) {
            throw new RuntimeException('ไม่สามารถคัดลอกไปยังเอเจนต์เดิมได้');
        }

        $source->loadMissing(['address', 'tags', 'asset_images.image']);

        return DB::transaction(function () use ($source, $targetAgentId, $targetAssetTypeId, $createdBy) {
            $addressId = $this->duplicateAddress($source->address);

            $attributes = collect($source->getAttributes())
                ->except([
                    'id',
                    'address_id',
                    'agent_id',
                    'view_count',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ])
                ->all();

            foreach (self::YN_FIELDS as $field) {
                if (array_key_exists($field, $attributes)) {
                    $attributes[$field] = $this->normalizeYn($attributes[$field]);
                }
            }

            $priceAdaptation = $this->profileTransfer->adaptPricesForAgentTransition(
                $source,
                $source->agent_id,
                $targetAgentId,
            );

            $copy = Asset::query()->create([
                ...$attributes,
                ...$priceAdaptation,
                'code' => $this->resolveUniqueCode((string) $source->code, $targetAgentId),
                'address_id' => $addressId,
                'agent_id' => $targetAgentId,
                'asset_type_id' => $targetAssetTypeId,
                'view_count' => 0,
                'isrecommend' => 'N',
                'created' => now(),
                'createdby' => $createdBy,
            ]);

            $this->duplicateTags($source, $copy);
            $this->duplicateImages($source, $copy);

            return $copy->fresh(['address', 'tags', 'asset_images.image', 'agent']);
        });
    }

    private function duplicateAddress(?Address $address): ?string
    {
        if ($address === null) {
            return null;
        }

        $duplicate = Address::query()->create([
            'address1' => $address->address1,
            'address2' => $address->address2,
            'moo' => $address->moo,
            'soi' => $address->soi,
            'street' => $address->street,
            'district' => $address->district,
            'amphur' => $address->amphur,
            'province' => $address->province,
            'zipcode' => $address->zipcode,
            'description' => $address->description,
            'created' => now(),
        ]);

        return $duplicate->id;
    }

    private function duplicateTags(Asset $source, Asset $copy): void
    {
        $copy->tags()->sync($source->tags->pluck('id')->all());
    }

    private function duplicateImages(Asset $source, Asset $copy): void
    {
        $targetDirectory = Asset::picDirectory($copy->id);
        $targetPath = public_path($targetDirectory);
        File::ensureDirectoryExists($targetPath);

        foreach ($source->asset_images as $assetImage) {
            $image = $assetImage->image;

            if ($image === null || blank($image->img_path)) {
                continue;
            }

            $sourcePath = $this->resolveSourcePath($image->img_path, $source->id);

            if ($sourcePath === null || ! is_file($sourcePath)) {
                continue;
            }

            $extension = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'webp';
            $filename = Str::uuid()->toString().'.'.$extension;
            $relativePath = $targetDirectory.'/'.$filename;

            if (! copy($sourcePath, $targetPath.DIRECTORY_SEPARATOR.$filename)) {
                continue;
            }

            $newImage = Image::query()->create([
                'name' => $filename,
                'type' => $image->type ?? 'property',
                'img_path' => $relativePath,
                'created' => now(),
            ]);

            AssetImage::query()->create([
                'asset_id' => $copy->id,
                'image_id' => $newImage->id,
                'isdefault' => $assetImage->isdefault,
                'seq' => $assetImage->seq,
                'created' => now(),
            ]);

            $this->imageProxy->warmAllVariants($newImage, 'property_copy');
        }
    }

    private function resolveSourcePath(string $imgPath, string $sourceAssetId): ?string
    {
        if (str_starts_with($imgPath, '/') && is_file($imgPath)) {
            return $imgPath;
        }

        $publicPath = public_path(ltrim($imgPath, '/'));

        if (is_file($publicPath)) {
            return $publicPath;
        }

        $managedPrefix = Asset::picDirectory($sourceAssetId).'/';
        $basename = basename($imgPath);

        $managedPath = public_path($managedPrefix.$basename);

        return is_file($managedPath) ? $managedPath : null;
    }

    private function resolveUniqueCode(string $code, string $agentId): string
    {
        $code = trim($code);

        if ($code === '') {
            $code = 'COPY';
        }

        if (! $this->codeExists($agentId, $code)) {
            return $code;
        }

        $suffix = 2;

        while ($this->codeExists($agentId, "{$code}-{$suffix}")) {
            $suffix++;
        }

        return "{$code}-{$suffix}";
    }

    private function codeExists(string $agentId, string $code): bool
    {
        return Asset::query()
            ->where('agent_id', $agentId)
            ->where('code', $code)
            ->exists();
    }

    private function normalizeYn(mixed $value, string $default = 'N'): string
    {
        if ($value === null || $value === '') {
            return $default;
        }

        $normalized = strtoupper(trim((string) $value));

        return in_array($normalized, ['Y', '1', 'TRUE', 'YES'], true) ? 'Y' : 'N';
    }
}
