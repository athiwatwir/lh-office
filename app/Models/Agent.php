<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Services\ImageUploadService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class Agent
 *
 * @property string $id
 * @property string $name
 * @property string|null $code
 * @property string|null $logo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|Asset[] $assets
 *
 * @package App\Models
 */
class Agent extends Model
{
    public const LOGO_DIRECTORY = 'upload/agent';

    use HasUuids;

    protected $table = 'agents';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'logo',
        'api_key',
        'system_type'
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function isInUse(): bool
    {
        return $this->assets()->exists();
    }

    public function setCodeAttribute(?string $value): void
    {
        $this->attributes['code'] = blank($value) ? null : Str::upper(trim($value));
    }

    public function getCodeAttribute(?string $value): ?string
    {
        return blank($value) ? null : Str::upper($value);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (blank($this->logo)) {
            return null;
        }

        $localUrl = app(ImageUploadService::class)->url($this->logo, self::LOGO_DIRECTORY);

        return $localUrl ?? Image::resolveLegacyUrl($this->logo);
    }
}
