<?php

namespace App\Services;

readonly class ImageUploadOptions
{
    public function __construct(
        public string $directory = 'upload',
        public int $quality = 85,
        public ?int $maxWidth = null,
        public ?int $maxHeight = null,
        public bool $maintainAspectRatio = true,
    ) {}
}
