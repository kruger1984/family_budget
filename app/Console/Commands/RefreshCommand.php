<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('budget:refresh')]
#[Description('Refresh')]
class RefreshCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'budget:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh';

    public function handle(): int
    {
        if (app()->isProduction()) {
            return self::FAILURE;
        }

        Cache::flush();

        $this->call('migrate:fresh', [
            '--seed' => true,
        ]);

        $this->info('Creating Filament user...');

        $this->call('make:filament-user', [
            '--name' => 'Admin',
            '--email' => 'admin@example.com',
            '--password' => 'password',
        ]);

        $this->info('name: Admin');
        $this->info('email: admin@example.com');
        $this->info('password: password');

        return self::SUCCESS;
    }
}
