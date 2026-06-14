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
 * Class Option
 * 
 * @property string $id
 * @property string $name
 * @property string $type
 * @property Carbon|null $created
 * @property int|null $seq
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Collection|Asset[] $assets
 *
 * @package App\Models
 */
class Option extends Model
{
	use SoftDeletes;
	use HasUuids;
	protected $table = 'options';
	public $incrementing = false;

	protected $casts = [
		'created' => 'datetime',
		'seq' => 'int'
	];

	protected $fillable = [
		'name',
		'type',
		'created',
		'seq'
	];

	public function assets()
	{
		return $this->belongsToMany(Asset::class, 'asset_options')
					->withPivot('id', 'deleted_at')
					->withTimestamps();
	}
}
