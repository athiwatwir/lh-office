<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Services\ImageUploadService;
use Carbon\Carbon;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 *
 * @property string $id
 * @property string|null $usercode
 * @property string|null $title
 * @property string $firstname
 * @property string $lastname
 * @property string|null $username
 * @property string|null $password
 * @property string $email
 * @property string|null $phone
 * @property string|null $lineid
 * @property string|null $fax
 * @property string|null $isactive
 * @property string|null $isverify
 * @property string|null $islocked
 * @property string|null $iscustomer
 * @property string|null $isseller
 * @property string|null $gender
 * @property Carbon|null $created
 * @property Carbon|null $updated
 * @property string|null $verifycode
 * @property string|null $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read string $name
 *
 * @property string|null $image_id
 * @property Image|null $image
 *
 * @property Collection|Asset[] $assets
 * @property Collection|Address[] $addresses
 *
 * @package App\Models
 */
class User extends Authenticatable implements MustVerifyEmail
{
    public const PIC_DIRECTORY = 'upload/user';

    use HasUuids;
    use MustVerifyEmailTrait;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'users';

    public $incrementing = false;

    protected $casts = [
        'created' => 'datetime',
        'updated' => 'datetime',
        'seq' => 'int',
    ];

    protected $hidden = [
        'password',
        'verifycode',
    ];

    protected $fillable = [
        'usercode',
        'title',
        'firstname',
        'lastname',
        'name',
        'username',
        'password',
        'email',
        'phone',
        'lineid',
        'fax',
        'isactive',
        'isverify',
        'islocked',
        'iscustomer',
        'isseller',
        'gender',
        'created',
        'updated',
        'verifycode',
        'position',
        'image_id',
        'seq'
    ];

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            if (blank($user->email) || str_contains($user->email, '_delete')) {
                return;
            }

            $user->email = self::emailForDeletion($user->email, $user->id);
            $user->saveQuietly();
        });
    }

    public static function emailForDeletion(string $email, string $id): string
    {
        $suffix = '_delete_'.$id;
        $maxLength = 255;

        if (strlen($email) + strlen($suffix) > $maxLength) {
            $email = substr($email, 0, $maxLength - strlen($suffix));
        }

        return $email.$suffix;
    }

    public function getNameAttribute(): string
    {
        return trim("{$this->firstname} {$this->lastname}");
    }

    public function setNameAttribute(string $value): void
    {
        $parts = preg_split('/\s+/', trim($value), 2);

        $this->attributes['firstname'] = $parts[0] ?? '';
        $this->attributes['lastname'] = $parts[1] ?? '';
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->isverify === 'Y';
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill(['isverify' => 'Y'])->save();
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        if (! $this->relationLoaded('image')) {
            $this->load('image');
        }

        $image = $this->image;

        if ($image === null) {
            return null;
        }

        $storagePath = $image->img_path ?: ($image->getAttributes()['path'] ?? null);

        if (blank($storagePath)) {
            return null;
        }

        if (str_starts_with($storagePath, self::PIC_DIRECTORY . '/')) {
            return app(ImageUploadService::class)->url(basename($storagePath), self::PIC_DIRECTORY)
                ?? $image->url;
        }

        $localUrl = app(ImageUploadService::class)->url(basename($storagePath), self::PIC_DIRECTORY);

        return $localUrl ?? $image->url;
    }

    public function isInUse(): bool
    {
        return $this->assets()->exists();
    }

    public function isActive(): bool
    {
        return $this->isactive === 'Y';
    }

    public function isSeller(): bool
    {
        return $this->isseller === 'Y';
    }

    public function scopeSellers($query)
    {
        return $query->where('isseller', 'Y');
    }

    public function scopeSystemUsers($query)
    {
        return $query->where(function ($builder) {
            $builder
                ->whereNull('isseller')
                ->orWhere('isseller', '!=', 'Y');
        });
    }

    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'user_addresses')
            ->withPivot('id', 'created', 'description');
    }
}
