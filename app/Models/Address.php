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
 * Class Address
 *
 * @property string $id
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $moo
 * @property string|null $soi
 * @property string|null $district
 * @property string|null $amphur
 * @property string|null $province
 * @property string|null $street
 * @property string|null $zipcode
 * @property Carbon|null $created
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property Collection|Asset[] $assets
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Address extends Model
{
    use SoftDeletes;
    use HasUuids;
    protected $table = 'addresses';
    public $incrementing = false;

    protected $casts = [
        'created' => 'datetime'
    ];

    protected $fillable = [
        'address1',
        'address2',
        'moo',
        'soi',
        'district',
        'amphur',
        'street',
        'zipcode',
        'created',
        'description',
        'province',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_addresses')
            ->withPivot('id', 'created', 'description');
    }
}
