<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Asset
 *
 * @property string $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $asset_type_id
 * @property string|null $asset_type_des
 * @property string $user_id
 * @property Carbon|null $created
 * @property string|null $createdby
 * @property float|null $floor_total
 * @property int|null $bedroom
 * @property int|null $bathroom
 * @property int|null $kitchen_room
 * @property int|null $reception_room
 * @property int|null $dining_room
 * @property int|null $maid_room
 * @property int|null $parking
 * @property float|null $area_rai
 * @property float|null $area_ngan
 * @property float|null $area_wah
 * @property float|null $area_meter
 * @property float|null $price_per_wah
 * @property float|null $price_amounnt
 * @property string|null $option
 * @property string|null $address_id
 * @property string $zone_id
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $isspecial_marketprice
 * @property string|null $isspecial_appraised
 * @property float|null $area_width
 * @property float|null $area_long
 * @property string $iscovering
 * @property string $isdweller
 * @property string|null $direction
 * @property float|null $price_amounnt_lower
 * @property string $issale
 * @property string $isrent
 * @property string $issalerent
 * @property string $issellout
 * @property string $issaledown
 * @property float|null $floor
 * @property float|null $price_rent
 * @property string|null $youtube_link
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property Address|null $address
 * @property User $user
 * @property AssetType $asset_type
 * @property Zone $zone
 * @property Collection|Image[] $images
 * @property Collection|Option[] $options
 *
 * @package App\Models
 */
class Asset extends Model
{
    use SoftDeletes;
    use HasUuids;
    protected $table = 'assets';
    public $incrementing = false;

    protected $casts = [
        'created' => 'datetime',
        'floor_total' => 'float',
        'bedroom' => 'int',
        'bathroom' => 'int',
        'kitchen_room' => 'int',
        'reception_room' => 'int',
        'dining_room' => 'int',
        'maid_room' => 'int',
        'parking' => 'int',
        'area_rai' => 'float',
        'area_ngan' => 'float',
        'area_wah' => 'float',
        'area_meter' => 'float',
        'price_per_wah' => 'float',
        'price_amounnt' => 'float',
        'area_width' => 'float',
        'area_long' => 'float',
        'price_amounnt_lower' => 'float',
        'floor' => 'float',
        'price_rent' => 'float'
    ];

    protected $fillable = [
        'code',
        'name',
        'description',
        'asset_type_id',
        'asset_type_des',
        'user_id',
        'created',
        'createdby',
        'floor_total',
        'bedroom',
        'bathroom',
        'kitchen_room',
        'reception_room',
        'dining_room',
        'maid_room',
        'parking',
        'area_rai',
        'area_ngan',
        'area_wah',
        'area_meter',
        'price_per_wah',
        'price_amounnt',
        'option',
        'address_id',
        'zone_id',
        'latitude',
        'longitude',
        'isspecial_marketprice',
        'isspecial_appraised',
        'area_width',
        'area_long',
        'iscovering',
        'isdweller',
        'direction',
        'price_amounnt_lower',
        'issale',
        'isrent',
        'issalerent',
        'issellout',
        'issaledown',
        'floor',
        'price_rent',
        'youtube_link',
        'agent_id',
        'isactive'
    ];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asset_type()
    {
        return $this->belongsTo(AssetType::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function images()
    {
        return $this->belongsToMany(Image::class, 'asset_images')
            ->withPivot('id', 'isdefault', 'created', 'seq', 'deleted_at')
            ->withTimestamps();
    }

    public function options()
    {
        return $this->belongsToMany(Option::class, 'asset_options')
            ->withPivot('id', 'deleted_at')
            ->withTimestamps();
    }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['code'] !== '', fn(Builder $builder) => $builder->where('code', 'like', '%' . $filters['code'] . '%'))
            ->when($filters['name'] !== '', function (Builder $builder) use ($filters) {
                $pattern = str_contains($filters['name'], '%')
                    ? $filters['name']
                    : '%' . $filters['name'] . '%';

                $builder->where('name', 'like', $pattern);
            })
            ->when($filters['asset_type_id'] !== '', fn(Builder $builder) => $builder->where('asset_type_id', $filters['asset_type_id']))
            ->when($filters['zone_id'] !== '', fn(Builder $builder) => $builder->where('zone_id', $filters['zone_id']))
            ->when($filters['user_id'] !== '', fn(Builder $builder) => $builder->where('user_id', $filters['user_id']));
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created')->orderByDesc('created_at');
    }
}
