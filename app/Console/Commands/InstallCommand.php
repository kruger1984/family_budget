<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class InstallCommand extends Command
{
    protected $signature = 'budget:install';

    protected $description = 'Initial installation of the application';

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

        $this->info('Creating Super Admin user...');

        $name = text('Name', default: 'Admin', required: true);
        $email = text('Email address', required: true);
        $password = password('Password', required: true);

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);

        $this->info("Installation completed! You can now login with {$email}");

        return self::SUCCESS;
    }
}
