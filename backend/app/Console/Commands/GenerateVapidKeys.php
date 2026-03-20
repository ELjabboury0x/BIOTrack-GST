<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;
use Throwable;

class GenerateVapidKeys extends Command
{
    protected $signature = 'push:vapid';

    protected $description = 'Generate VAPID keys for Web Push notifications';

    public function handle(): int
    {
        try {
            $keys = VAPID::createVapidKeys();
        } catch (Throwable $exception) {
            $this->error('Unable to create VAPID keys with current PHP/OpenSSL setup.');
            $this->line('Fallback: run `npx web-push generate-vapid-keys` and copy values to `.env`.');
            $this->line('Error: ' . $exception->getMessage());

            return self::FAILURE;
        }

        $this->line('Add these values to your .env:');
        $this->newLine();
        $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        $this->line('VAPID_SUBJECT=mailto:admin@example.com');

        return self::SUCCESS;
    }
}
