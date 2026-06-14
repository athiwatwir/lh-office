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
 * Class Amphur
 * 
 * @property string $id
 * @property string|null $amphur_code
 * @property string|null $amphur_name
 * @property string|null $postcode
 * @property int|null $geo_id
 * @property string|null $province_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class Amphur extends Model
{
	use SoftDeletes;
	use HasUuids;
	protected $table = 'amphurs';
	public $incrementing = false;

	protected $casts = [
		'geo_id' => 'int'
	];

	protected $fillable = [
		'amphur_code',
		'amphur_name',
		'postcode',
		'geo_id',
		'province_id'
	];
}
