<?php

namespace App\Services;

use App\Models\Image;
use App\Models\User;
use App\Models\Useimage;
use Illuminate\Http\UploadedFile;

class UserImageService
{
    public function __construct(
        private readonly ImageUploadService $imageUpload,
    ) {}

    public function attach(User $user, UploadedFile $file): void
    {
        $filename = $this->imageUpload->store($file, $this->uploadOptions());
        $path = User::PIC_DIRECTORY.'/'.$filename;

        $image = Image::query()->create([
            'name' => $filename,
            'type' => 'user',
            'path' => $path,
            'created' => now(),
        ]);

        Useimage::query()->create([
            'image_id' => $image->id,
            'user_id' => $user->id,
            'created' => now(),
        ]);
    }

    public function replace(User $user, UploadedFile $file): void
    {
        $this->deleteLocalProfileImage($user);
        $this->attach($user, $file);
    }

    public function deleteLocalProfileImage(User $user): void
    {
        $useimage = $user->useimages()
            ->with('image')
            ->latest('created')
            ->first();

        if ($useimage?->image === null) {
            return;
        }

        $this->deleteManagedFile($useimage->image->path);
        $useimage->image->delete();
        $useimage->delete();
    }

    private function deleteManagedFile(?string $path): void
    {
        if (blank($path) || ! $this->isManagedPath($path)) {
            return;
        }

        $this->imageUpload->delete(basename($path), User::PIC_DIRECTORY);
    }

    private function isManagedPath(string $path): bool
    {
        return str_starts_with($path, User::PIC_DIRECTORY.'/')
            || str_starts_with($path, '/'.User::PIC_DIRECTORY.'/');
    }

    private function uploadOptions(): ImageUploadOptions
    {
        return new ImageUploadOptions(
            directory: User::PIC_DIRECTORY,
            quality: 85,
            maxWidth: 400,
            maxHeight: 400,
        );
    }
}
