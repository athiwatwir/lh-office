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
 * Class Province
 * 
 * @property string $id
 * @property string|null $province_code
 * @property string|null $province_name
 * @property int|null $geoid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Collection|Address[] $addresses
 *
 * @package App\Models
 */
class Province extends Model
{
	use SoftDeletes;
	use HasUuids;
	protected $table = 'provinces';
	public $incrementing = false;

	protected $casts = [
		'geoid' => 'int'
	];

	protected $fillable = [
		'province_code',
		'province_name',
		'geoid'
	];

	public function addresses()
	{
		return $this->hasMany(Address::class);
	}
}
