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
 * @property Collection|Asset[] $assets
 * @property Collection|Useimage[] $useimages
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
    ];

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

	public function useimages()
	{
		return $this->hasMany(Useimage::class);
	}

	public function getProfileImageUrlAttribute(): ?string
	{
		if (! $this->relationLoaded('useimages')) {
			$this->load([
				'useimages' => fn ($query) => $query->latest('created')->limit(1),
				'useimages.image',
			]);
		}

		$path = $this->useimages->first()?->image?->img_path;

		if (blank($path)) {
			return null;
		}

		if (str_starts_with($path, self::PIC_DIRECTORY.'/')) {
			return app(ImageUploadService::class)->url(basename($path), self::PIC_DIRECTORY);
		}

		$localUrl = app(ImageUploadService::class)->url(basename($path), self::PIC_DIRECTORY);

		return $localUrl ?? Image::resolveLegacyUrl($path);
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

    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'user_addresses')
            ->withPivot('id', 'created', 'description');
    }
}
