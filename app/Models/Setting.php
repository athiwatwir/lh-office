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
 * Class Setting
 * 
 * @property string $id
 * @property string|null $email_receiver_contact
 * @property string|null $email_receiver_seller
 * @property string|null $email_receiver_purchase
 * @property Carbon|null $created
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class Setting extends Model
{
	use SoftDeletes;
	use HasUuids;
	protected $table = 'settings';
	public $incrementing = false;

	protected $casts = [
		'created' => 'datetime'
	];

	protected $fillable = [
		'email_receiver_contact',
		'email_receiver_seller',
		'email_receiver_purchase',
		'created'
	];
}
