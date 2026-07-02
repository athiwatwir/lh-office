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
 * @property string|null $image_id
 * @property string|null $agent_id
 * @property Carbon|null $created
 * @property Carbon|null $updated
 * @property string|null $createdby
 * @property string|null $isactive
 * @property string $category_id
 * @property int|null $seq
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read string|null $cover_image_url
 *
 * @property Agent|null $agent
 * @property Category $category
 * @property Image|null $image
 *
 * @package App\Models
 */
class Article extends Model
{
    public const COVER_DIRECTORY = 'upload/article';

    use SoftDeletes;
    use HasUuids;

    protected $table = 'articles';

    public $incrementing = false;

    protected $casts = [
        'created' => 'datetime',
        'updated' => 'datetime',
        'seq' => 'int',
    ];

    protected $fillable = [
        'name',
        'text',
        'image_id',
        'agent_id',
        'created',
        'updated',
        'createdby',
        'isactive',
        'category_id',
        'seq',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (! $this->relationLoaded('image')) {
            $this->load('image');
        }

        return $this->image?->url;
    }

    public function isVisibleToAllAgents(): bool
    {
        return blank($this->agent_id);
    }

    public function scopeActive($query)
    {
        return $query->where('isactive', 'Y');
    }

    public function scopeVisibleToAgent($query, string $agentId)
    {
        return $query->where(function ($builder) use ($agentId) {
            $builder
                ->whereNull('agent_id')
                ->orWhere('agent_id', $agentId);
        });
    }

    public function scopeOrderedForDisplay($query)
    {
        return $query
            ->orderByRaw('seq IS NULL')
            ->orderBy('seq')
            ->orderByDesc('updated');
    }
}
