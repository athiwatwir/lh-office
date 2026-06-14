<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserAddress
 * 
 * @property string $id
 * @property string $user_id
 * @property string $address_id
 * @property Carbon|null $created
 * @property string|null $description
 * 
 * @property Address $address
 * @property User $user
 *
 * @package App\Models
 */
class UserAddress extends Model
{
	use HasUuids;
	protected $table = 'user_addresses';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'created' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'address_id',
		'created',
		'description'
	];

	public function address()
	{
		return $this->belongsTo(Address::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
