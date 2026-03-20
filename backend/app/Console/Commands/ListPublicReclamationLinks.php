<?php

namespace App\Console\Commands;

use App\Models\Service;
use Illuminate\Console\Command;

class ListPublicReclamationLinks extends Command
{
    protected $signature = 'reclamation:list-links {--base-url=http://127.0.0.1:8000}';

    protected $description = 'List all public reclamation links by service code';

    public function handle(): int
    {
        $baseUrl = rtrim((string) $this->option('base-url'), '/');

        $services = Service::query()
            ->whereNotNull('code')
            ->whereRaw('TRIM(code) <> ""')
            ->orderBy('name')
            ->get(['name', 'code']);

        $this->info('Total liens: ' . $services->count());
        $this->line($baseUrl . '/reclamation');

        foreach ($services as $service) {
            $this->line(($service->code ?: '-') . ' | ' . ($service->name ?: '-') . ' | ' . $baseUrl . '/reclamation/' . urlencode((string) $service->code));
        }

        return self::SUCCESS;
    }
}
