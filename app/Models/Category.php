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
 * Class Category
 * 
 * @property string $id
 * @property string $name
 * @property string|null $decription
 * @property string|null $isactive
 * @property int|null $seq
 * @property Carbon|null $created
 * @property string|null $createdby
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Collection|Article[] $articles
 *
 * @package App\Models
 */
class Category extends Model
{
	use SoftDeletes;
	use HasUuids;
	protected $table = 'categories';
	public $incrementing = false;

	protected $casts = [
		'seq' => 'int',
		'created' => 'datetime'
	];

	protected $fillable = [
		'name',
		'decription',
		'isactive',
		'seq',
		'created',
		'createdby'
	];

	public function articles()
	{
		return $this->hasMany(Article::class);
	}
}
