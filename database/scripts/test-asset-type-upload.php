<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AssetType;
use App\Services\AssetTypeImageService;
use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;

$service = app(AssetTypeImageService::class);
$upload = app(ImageUploadService::class);

echo 'webp supported: '.(function_exists('imagewebp') ? 'yes' : 'no').PHP_EOL;
echo 'dir exists/writable: ';
$dir = public_path('upload/property-type');
echo (is_dir($dir) ? 'dir yes' : 'dir no').' / '.(is_writable($dir) || is_writable(public_path('upload')) || is_writable(public_path()) ? 'writable' : 'not writable').PHP_EOL;

$assetType = AssetType::query()->first();
if ($assetType === null) {
    echo "no asset type\n";
    exit(1);
}

// create tiny png in memory
$img = imagecreatetruecolor(10, 10);
$tmp = tempnam(sys_get_temp_dir(), 'img');
imagepng($img, $tmp);
imagedestroy($img);

$file = new UploadedFile($tmp, 'test.png', 'image/png', null, true);

try {
    $service->attach($assetType, $file);
    $assetType->refresh();
    $assetType->load('image');
    echo 'image_id: '.$assetType->image_id.PHP_EOL;
    echo 'image_url: '.$assetType->image_url.PHP_EOL;
    echo 'file exists: '.(is_file(public_path($assetType->image?->img_path)) ? 'yes' : 'no').PHP_EOL;
} catch (Throwable $e) {
    echo 'ERROR: '.$e->getMessage().PHP_EOL;
    echo $e->getTraceAsString().PHP_EOL;
}

@unlink($tmp);
