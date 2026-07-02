<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Services\ImageProxyService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Image
 *
 * @property string $id
 * @property string $name
 * @property string|null $type
 * @property string|null $img_path
 * @property Carbon|null $created
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property Collection|Asset[] $assets
 * @property Collection|Useimage[] $useimages
 *
 * @package App\Models
 */
class Image extends Model
{
    use SoftDeletes;
    use HasUuids;
    protected $table = 'images';
    public $incrementing = false;

    protected $casts = [
        'created' => 'datetime'
    ];

    protected $fillable = [
        'name',
        'type',
        'created',
        'img_path',
    ];

    public function assets()
    {
        return $this->belongsToMany(Asset::class, 'asset_images')
            ->withPivot('id', 'isdefault', 'created', 'seq', 'deleted_at')
            ->withTimestamps();
    }

    public function useimages()
    {
        return $this->hasMany(Useimage::class);
    }

    public function getUrlAttribute(): ?string
    {
        if (blank($this->img_path)) {
            return null;
        }

        if (str_starts_with($this->img_path, 'http://') || str_starts_with($this->img_path, 'https://')) {
            return $this->img_path;
        }

        $localPath = app(ImageProxyService::class)->resolveSourceFilePath($this->img_path);

        if ($localPath === null) {
            return null;
        }

        $publicRoot = realpath(public_path()) ?: public_path();
        $realLocalPath = realpath($localPath);

        if ($realLocalPath !== false && str_starts_with($realLocalPath, $publicRoot)) {
            return '/'.ltrim(str_replace('\\', '/', substr($realLocalPath, strlen($publicRoot))), '/');
        }

        return null;
    }

    public function proxyUrl(?int $width = null, ?int $height = null, ?int $quality = null, bool $absolute = false): ?string
    {
        if (blank($this->id)) {
            return null;
        }

        $params = array_filter([
            'w' => $width,
            'h' => $height,
            'q' => $quality !== null && $quality !== (int) config('image.default_quality', 80) ? $quality : null,
        ], fn ($value) => $value !== null);

        $path = route('image.proxy', ['image' => $this->id], $absolute);

        if ($params === []) {
            return $path;
        }

        return $path.'?'.http_build_query($params);
    }

    public function thumbnailUrl(bool $absolute = false): ?string
    {
        return app(ImageProxyService::class)->publicCacheUrl($this, ImageProxyService::VARIANT_THUMB, $absolute);
    }

    public function galleryUrl(bool $absolute = false): ?string
    {
        return app(ImageProxyService::class)->publicCacheUrl($this, ImageProxyService::VARIANT_GALLERY, $absolute);
    }

    public static function resolveLegacyUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $baseUrl = rtrim((string) config('app.legacy_image_base_url'), '/');

        if ($baseUrl === '') {
            return null;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'upload/')) {
            $relative = substr($normalized, strlen('upload/'));

            return $baseUrl.'/'.$relative;
        }

        return $baseUrl.'/'.basename($path);
    }
}
