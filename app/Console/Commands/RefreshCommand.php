<?php

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

//        $storage = Storage::disk('images');
//
//        $storage->deleteDirectory('images/products');
//        $storage->deleteDirectory('images/brands');

        $this->call('migrate:fresh', [
            '--seed' => true
        ]);

//        $this->call('moonshine:user', [
//            '--username' => 'add.kononov@gmail.com',
//            '--name' => 'admin',
//            '--password' => '123987',
//        ]);


        return self::SUCCESS;
    }
}
