<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cols = Illuminate\Support\Facades\DB::select('SHOW COLUMNS FROM asset_types WHERE Field = ?', ['breatedby']);
print_r($cols);

$cols2 = Illuminate\Support\Facades\DB::select('SHOW COLUMNS FROM asset_types WHERE Field = ?', ['image_id']);
print_r($cols2);
