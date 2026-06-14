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
 * Class Contact
 * 
 * @property string $id
 * @property string|null $full_name
 * @property string|null $tel
 * @property string|null $email
 * @property string|null $message
 * @property Carbon|null $created
 * @property string|null $createdby
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class Contact extends Model
{
	use SoftDeletes;
	use HasUuids;
	protected $table = 'contacts';
	public $incrementing = false;

	protected $casts = [
		'created' => 'datetime'
	];

	protected $fillable = [
		'full_name',
		'tel',
		'email',
		'message',
		'created',
		'createdby'
	];
}
