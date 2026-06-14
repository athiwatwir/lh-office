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
 * Class Article
 * 
 * @property string $id
 * @property string $name
 * @property string|null $text
 * @property Carbon|null $created
 * @property Carbon|null $updated
 * @property string|null $createdby
 * @property string|null $isactive
 * @property string $category_id
 * @property int|null $seq
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Category $category
 *
 * @package App\Models
 */
class Article extends Model
{
	use SoftDeletes;
	use HasUuids;
	protected $table = 'articles';
	public $incrementing = false;

	protected $casts = [
		'created' => 'datetime',
		'updated' => 'datetime',
		'seq' => 'int'
	];

	protected $fillable = [
		'name',
		'text',
		'created',
		'updated',
		'createdby',
		'isactive',
		'category_id',
		'seq'
	];

	public function category()
	{
		return $this->belongsTo(Category::class);
	}
}
