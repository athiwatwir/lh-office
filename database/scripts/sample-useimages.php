<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('useimages')
    ->join('images', 'images.id', '=', 'useimages.image_id')
    ->select('images.path', 'images.type', 'useimages.user_id')
    ->limit(5)
    ->get();

print_r($rows->toArray());
