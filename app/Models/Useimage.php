<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Useimage
 * 
 * @property string $id
 * @property string $image_id
 * @property string|null $user_id
 * @property Carbon|null $created
 * 
 * @property Image $image
 * @property User|null $user
 *
 * @package App\Models
 */
class Useimage extends Model
{
	use HasUuids;
	protected $table = 'useimages';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'created' => 'datetime'
	];

	protected $fillable = [
		'image_id',
		'user_id',
		'created'
	];

	public function image()
	{
		return $this->belongsTo(Image::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
