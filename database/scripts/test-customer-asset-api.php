<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\V1\CustomerAssetController;
use App\Http\Middleware\AuthenticateAgentApiKey;
use App\Http\Requests\Api\StoreCustomerAssetRequest;
use App\Models\Agent;
use App\Models\AssetType;

$agent = Agent::query()->whereNotNull('api_key')->where('api_key', '!=', '')->first();
$assetType = AssetType::query()->first();

if (! $agent || ! $assetType) {
    echo "missing agent api key or asset type\n";
    exit(1);
}

$payload = [
    'type' => 'sell',
    'asset_type_id' => $assetType->id,
    'price_amount' => 3500000,
    'description' => 'API test request',
    'customer' => [
        'fullname' => 'ทดสอบ API',
        'tel' => '0899999999',
        'email' => 'test@example.com',
    ],
    'address' => [
        'district' => 'บางนา',
        'amphur' => 'บางนา',
        'province_name' => 'กรุงเทพมหานคร',
        'zipcode' => '10260',
    ],
];

$request = StoreCustomerAssetRequest::create('/api/v1/customer-assets', 'POST', $payload);
$request->attributes->set(AuthenticateAgentApiKey::REQUEST_ATTRIBUTE, $agent);
$request->setContainer($app);
$request->setRedirector($app->make('redirect'));
$request->validateResolved();

$response = $app->make(CustomerAssetController::class)->store($request);
echo $response->getStatusCode().PHP_EOL;
echo $response->getContent().PHP_EOL;
