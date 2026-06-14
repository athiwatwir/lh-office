<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class AssetOption
 * 
 * @property string $id
 * @property string $asset_id
 * @property string $option_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Asset $asset
 * @property Option $option
 *
 * @package App\Models
 */
class AssetOption extends Model
{
	use SoftDeletes;
	use HasUuids;
	protected $table = 'asset_options';
	public $incrementing = false;

	protected $fillable = [
		'asset_id',
		'option_id'
	];

	public function asset()
	{
		return $this->belongsTo(Asset::class);
	}

	public function option()
	{
		return $this->belongsTo(Option::class);
	}
}
