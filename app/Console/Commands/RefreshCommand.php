<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

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

        $this->info('Creating Super Admin user...');

        User::query()->updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        $this->info('Admin created successfully!');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', 'Admin'],
                ['Email', 'admin@example.com'],
                ['Password', 'password'],
            ]
        );

        return self::SUCCESS;
    }
}
