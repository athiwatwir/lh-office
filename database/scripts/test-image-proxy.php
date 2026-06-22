<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$image = App\Models\Image::query()
    ->where('img_path', 'like', 'upload/property/old/%')
    ->first(['id', 'img_path']);

if ($image === null) {
    echo "no image found\n";
    exit(1);
}

echo 'id: '.$image->id.PHP_EOL;
echo 'path: '.$image->img_path.PHP_EOL;
echo 'local: '.(is_file(public_path($image->img_path)) ? 'yes' : 'no').PHP_EOL;
echo 'thumb: '.$image->thumbnailUrl().PHP_EOL;

$service = app(App\Services\ImageProxyService::class);
$output = $service->render($image, 400, 300, 80);

echo 'render: '.($output ? strlen($output).' bytes' : 'failed').PHP_EOL;
