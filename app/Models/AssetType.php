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
 * @property string|null $pic
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
		'pic',
		'created',
		'breatedby',
		'seq'
	];

	public function assets()
	{
		return $this->hasMany(Asset::class);
	}

	public function customer_assets()
	{
		return $this->hasMany(CustomerAsset::class);
	}
}
