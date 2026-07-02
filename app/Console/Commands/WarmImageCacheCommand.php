<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Services\ImageProxyService;
use Illuminate\Console\Command;

class WarmImageCacheCommand extends Command
{
    protected $signature = 'images:warm-cache
                            {--variant= : thumb or gallery — default: both}
                            {--chunk=100 : Rows per batch}
                            {--force : Rebuild even when cache looks fresh}';

    protected $description = 'Pre-generate public WebP thumbnails for property images';

    public function handle(ImageProxyService $proxy): int
    {
        $variant = $this->option('variant');
        $variants = $variant !== null && $variant !== ''
            ? [$variant]
            : $proxy->variants();

        foreach ($variants as $name) {
            if (! in_array($name, $proxy->variants(), true)) {
                $this->error("Unknown variant: {$name}");

                return self::FAILURE;
            }
        }

        $chunk = max(1, (int) $this->option('chunk'));
        $force = (bool) $this->option('force');
        $processed = 0;
        $warmed = 0;
        $failed = 0;

        Image::query()
            ->whereNotNull('img_path')
            ->where('img_path', '!=', '')
            ->orderBy('id')
            ->chunk($chunk, function ($images) use ($proxy, $variants, $force, &$processed, &$warmed, &$failed): void {
                foreach ($images as $image) {
                    $processed++;

                    foreach ($variants as $variant) {
                        if ($force) {
                            $proxy->invalidate($image);
                        }

                        if ($proxy->warm($image, $variant, 'artisan')) {
                            $warmed++;
                        } else {
                            $failed++;
                            $this->warn("Failed: {$image->id} ({$variant}) — see storage/logs/laravel.log");
                        }
                    }
                }

                $this->line("Processed {$processed} images...");
            });

        $this->info("Done. Images: {$processed}, warmed: {$warmed}, failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
