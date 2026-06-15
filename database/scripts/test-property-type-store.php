<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\PropertyTypeController;
use App\Http\Requests\PropertyTypeRequest;
use App\Models\AssetType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

$user = User::query()->firstOrFail();
Auth::login($user);

$tmp = tempnam(sys_get_temp_dir(), 'img');
imagepng(imagecreatetruecolor(20, 20), $tmp);
$file = new UploadedFile($tmp, 'cover.png', 'image/png', null, true);

$name = 'Test Upload Type '.time();
$request = PropertyTypeRequest::create('/propertyType', 'POST', [
    'name' => $name,
    'seq' => 999,
]);
$request->files->set('image', $file);
$request->setContainer($app);
$request->setRedirector($app->make('redirect'));
$request->validateResolved();

echo 'hasFile: '.($request->hasFile('image') ? 'yes' : 'no').PHP_EOL;

$controller = $app->make(PropertyTypeController::class);
$controller->store($request);

$assetType = AssetType::query()->where('name', $name)->first();
$assetType?->load('image');

echo 'image_id: '.($assetType?->image_id ?? 'null').PHP_EOL;
echo 'image_url: '.($assetType?->image_url ?? 'null').PHP_EOL;

@unlink($tmp);
