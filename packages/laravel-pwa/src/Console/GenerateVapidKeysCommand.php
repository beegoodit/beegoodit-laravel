<?php

namespace BeegoodIT\LaravelPwa\Console;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeysCommand extends Command
{
    protected $signature = 'pwa:vapid-keys';

    protected $description = 'Generate VAPID keys for web push notifications';

    public function handle(): int
    {
        $this->info('Generating VAPID keys...');
        $this->newLine();

        $keys = VAPID::createVapidKeys();

        $this->line('Add the following to your <comment>.env</comment> file:');
        $this->newLine();

        $this->line('<fg=green>VAPID_PUBLIC_KEY=</>'.$keys['publicKey']);
        $this->line('<fg=green>VAPID_PRIVATE_KEY=</>'.$keys['privateKey']);
        $this->line('<fg=green>VAPID_SUBJECT=</>mailto:your-email@example.com');

        $this->newLine();
        $this->warn('⚠️  Keep the private key secret! Never commit it to version control.');

        $this->newLine();
        $this->info('For JavaScript, use the public key:');
        $this->line("const VAPID_PUBLIC_KEY = '{$keys['publicKey']}';");

        return Command::SUCCESS;
    }
}
