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
 * Class Customer
 * 
 * @property string $id
 * @property string $fullname
 * @property string $tel
 * @property string|null $email
 * @property string|null $lineid
 * @property Carbon|null $created
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Collection|CustomerAsset[] $customer_assets
 *
 * @package App\Models
 */
class Customer extends Model
{
	use SoftDeletes;
	use HasUuids;
	protected $table = 'customers';
	public $incrementing = false;

	protected $casts = [
		'created' => 'datetime'
	];

	protected $fillable = [
		'fullname',
		'tel',
		'email',
		'lineid',
		'created'
	];

	public function customer_assets()
	{
		return $this->hasMany(CustomerAsset::class);
	}
}
