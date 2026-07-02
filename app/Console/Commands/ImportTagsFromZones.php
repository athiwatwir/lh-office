<?php

namespace App\Console\Commands;

use App\Services\TagImportFromZonesService;
use Illuminate\Console\Command;

class ImportTagsFromZones extends Command
{
    protected $signature = 'tags:import-from-zones';

    protected $description = 'Import tags from zone description fields';

    public function handle(TagImportFromZonesService $import): int
    {
        $result = $import->import();

        if ($result['total'] === 0) {
            $this->warn('No names found in zone descriptions.');

            return self::SUCCESS;
        }

        $this->info("Total parsed: {$result['total']}");
        $this->info("Created: {$result['created']}");
        $this->info("Skipped (duplicate): {$result['skipped']}");

        return self::SUCCESS;
    }
}
