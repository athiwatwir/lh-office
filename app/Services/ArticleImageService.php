<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Image;
use Illuminate\Http\UploadedFile;

class ArticleImageService
{
    public function __construct(
        private readonly ImageUploadService $imageUpload,
    ) {}

    public function attach(Article $article, UploadedFile $file): void
    {
        $filename = $this->imageUpload->store($file, $this->uploadOptions());
        $path = Article::COVER_DIRECTORY.'/'.$filename;

        $image = Image::query()->create([
            'name' => $filename,
            'type' => 'article',
            'img_path' => $path,
            'created' => now(),
        ]);

        $article->update(['image_id' => $image->id]);
    }

    public function replace(Article $article, UploadedFile $file): void
    {
        $this->deleteLocalCover($article);
        $this->attach($article, $file);
    }

    public function deleteLocalCover(Article $article): void
    {
        $image = $article->image;

        if ($image === null) {
            return;
        }

        $this->deleteManagedFile($image->img_path);
        $article->update(['image_id' => null]);
        $image->delete();
    }

    private function deleteManagedFile(?string $path): void
    {
        if (blank($path) || ! $this->isManagedPath($path)) {
            return;
        }

        $this->imageUpload->delete(basename($path), Article::COVER_DIRECTORY);
    }

    private function isManagedPath(string $path): bool
    {
        return str_starts_with($path, Article::COVER_DIRECTORY.'/')
            || str_starts_with($path, '/'.Article::COVER_DIRECTORY.'/');
    }

    private function uploadOptions(): ImageUploadOptions
    {
        return new ImageUploadOptions(
            directory: Article::COVER_DIRECTORY,
            quality: 85,
            maxWidth: 1200,
            maxHeight: 630,
        );
    }
}
