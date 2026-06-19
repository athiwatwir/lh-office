<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AssetViewsDaily
 * 
 * @property int $id
 * @property string $asset_id
 * @property Carbon $view_date
 * @property int $total_views
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class AssetViewsDaily extends Model
{
	protected $table = 'asset_views_daily';

	protected $casts = [
		'view_date' => 'datetime',
		'total_views' => 'int'
	];

	protected $fillable = [
		'asset_id',
		'view_date',
		'total_views'
	];
}
