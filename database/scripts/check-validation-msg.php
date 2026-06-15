<?php
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo __('validation.uploaded', ['attribute' => 'test']).PHP_EOL;
echo __('validation.file', ['attribute' => 'test']).PHP_EOL;
