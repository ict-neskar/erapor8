<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Restore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:restore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //DB::select("DROP SCHEMA IF EXISTS ref CASCADE");
        //DB::select("DROP SCHEMA IF EXISTS dapodik CASCADE");
        //DB::select("DROP SCHEMA IF EXISTS public CASCADE");
        //DB::select("CREATE SCHEMA public");
        //$this->call('migrate:refresh');
        //$this->call('migrate:reset');
        //$this->call('backup:restore');
        $options = [
            '--backup' => 'latest',
            '--no-interaction' => TRUE,
            '--reset' => TRUE
        ];
        $this->call('backup:restore', $options);
        $this->call('app:update');
    }
}
