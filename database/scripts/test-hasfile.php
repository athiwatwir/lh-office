<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Requests\PropertyTypeRequest;
use Illuminate\Http\UploadedFile;

$tmp = tempnam(sys_get_temp_dir(), 'img');
imagepng(imagecreatetruecolor(20, 20), $tmp);
$file = new UploadedFile($tmp, 'cover.png', 'image/png', null, true);

$request = PropertyTypeRequest::create('/propertyType', 'POST', [
    'name' => 'Test',
    'seq' => 10,
], [], [], [], ['image' => $file]);

echo 'hasFile(image): '.($request->hasFile('image') ? 'yes' : 'no').PHP_EOL;
echo 'file(image) valid: '.($request->file('image')?->isValid() ? 'yes' : 'no').PHP_EOL;

// Symfony way to set files
$request2 = PropertyTypeRequest::create('/propertyType', 'POST', [
    'name' => 'Test',
    'seq' => 10,
]);
$request2->files->set('image', $file);
echo 'request2 hasFile(image): '.($request2->hasFile('image') ? 'yes' : 'no').PHP_EOL;

@unlink($tmp);
