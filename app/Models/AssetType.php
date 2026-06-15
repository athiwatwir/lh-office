<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class AssetType
 *
 * @property string $id
 * @property string $name
 * @property string|null $image_id
 * @property Image|null $image
 * @property-read string|null $image_url
 * @property Carbon|null $created
 * @property string|null $breatedby
 * @property int $seq
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property Collection|Asset[] $assets
 * @property Collection|CustomerAsset[] $customer_assets
 *
 * @package App\Models
 */
class AssetType extends Model
{
    public const PIC_DIRECTORY = 'upload/property-type';

    use SoftDeletes;
    use HasUuids;
    protected $table = 'asset_types';
    public $incrementing = false;

    protected $casts = [
        'created' => 'datetime',
        'seq' => 'int'
    ];

    protected $fillable = [
        'name',
        'created',
        'breatedby',
        'seq',
        'image_id',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function customer_assets()
    {
        return $this->hasMany(CustomerAsset::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function isInUse(): bool
    {
        return $this->assets()->exists() || $this->customer_assets()->exists();
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->relationLoaded('image')) {
            $this->load('image');
        }

        return $this->image?->url;
    }
}
