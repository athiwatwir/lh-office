<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AssetTag
 *
 * @property int $id
 * @property string $asset_id
 * @property int $tag_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class AssetTag extends Model
{
    protected $table = 'asset_tags';

    protected $casts = [
        'tag_id' => 'int'
    ];

    protected $fillable = [
        'asset_id',
        'tag_id'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
