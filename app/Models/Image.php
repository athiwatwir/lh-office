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
 * Class Image
 * 
 * @property string $id
 * @property string $name
 * @property string|null $type
 * @property string|null $path
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
		'path',
		'created'
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

		return $baseUrl.'/'.basename($path);
	}
}
