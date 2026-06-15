<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CustomerAsset
 *
 * @property string $id
 * @property string|null $zone_id
 * @property string $customer_id
 * @property string|null $description
 * @property string $asset_type_id
 * @property string|null $asset_type_des
 * @property Carbon|null $created
 * @property int|null $floor_total
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
 * @property string|null $isreqconsult
 * @property string $type
 * @property string|null $budgets
 * @property string|null $address_id
 *
 * @property Customer $customer
 * @property AssetType $asset_type
 * @property Zone|null $zone
 *
 * @package App\Models
 */
class CustomerAsset extends Model
{
    use HasUuids;
    protected $table = 'customer_assets';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'created' => 'datetime',
        'floor_total' => 'int',
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
        'price_amounnt' => 'float'
    ];

    protected $fillable = [
        'zone_id',
        'customer_id',
        'description',
        'asset_type_id',
        'asset_type_des',
        'created',
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
        'isreqconsult',
        'type',
        'budgets',
        'address_id',
        'agent_id',
        'isread'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function asset_type()
    {
        return $this->belongsTo(AssetType::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
