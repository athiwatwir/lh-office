<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Requests\PropertyTypeRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;

$tmp = tempnam(sys_get_temp_dir(), 'img');
$img = imagecreatetruecolor(10, 10);
imagepng($img, $tmp);
imagedestroy($img);

$file = new UploadedFile($tmp, 'test.png', 'image/png', null, true);

foreach (['image', 'cover', 'pic'] as $field) {
    $validator = Validator::make(
        ['name' => 'Test', 'seq' => 10, $field => $file],
        (new PropertyTypeRequest())->rules()
    );

    // patch rules for field name test
    $rules = [
        'name' => ['required', 'string', 'max:255'],
        'seq' => ['required', 'integer', 'min:0'],
        $field => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
    ];
    $validator = Validator::make(
        ['name' => 'Test', 'seq' => 10, $field => $file],
        $rules
    );

    echo $field.': '.($validator->fails() ? 'FAIL '.json_encode($validator->errors()->all()) : 'OK').PHP_EOL;
    echo 'hasFile simulation: '.($file->isValid() ? 'valid' : 'invalid').PHP_EOL;
}

@unlink($tmp);
