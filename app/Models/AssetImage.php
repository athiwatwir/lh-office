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
 * Class AssetImage
 * 
 * @property string $id
 * @property string $asset_id
 * @property string $image_id
 * @property string $isdefault
 * @property Carbon|null $created
 * @property int $seq
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Image $image
 * @property Asset $asset
 *
 * @package App\Models
 */
class AssetImage extends Model
{
	use SoftDeletes;
	use HasUuids;
	protected $table = 'asset_images';
	public $incrementing = false;

	protected $casts = [
		'created' => 'datetime',
		'seq' => 'int'
	];

	protected $fillable = [
		'asset_id',
		'image_id',
		'isdefault',
		'created',
		'seq'
	];

	public function image()
	{
		return $this->belongsTo(Image::class);
	}

	public function asset()
	{
		return $this->belongsTo(Asset::class);
	}
}
