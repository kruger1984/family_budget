<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'budget:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installation';

    public function handle(): int
    {
        $this->info('Starting installation...');

        $this->call('storage:link');

        $this->info('Running migrations...');
        $this->call('migrate', ['--force' => true]);

        $this->info('Installing Filament...');
        $this->call('filament:install', [
            '--panels' => true,
        ]);

        $this->info('Creating Filament user...');
        $this->call('make:filament-user');

        $this->info('Installation completed!');

        return self::SUCCESS;
    }
}
