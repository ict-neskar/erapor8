<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class Backup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup';

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
        $email = $this->ask('Email Administrator:');
        if($email){
            $user = User::where('email', $email)->first();
            if($user){
                $this->call('backup:run', [
                    '--only-db' => TRUE,
                    '--disable-notifications' => TRUE,
                ]);
            } else {
                $this->error('Email tidak ditemukan!');
                $this->call('app:backup');
            }
        } else {
            $this->error('Email admin tidak boleh kosong!');
            $this->call('app:backup');
        }
    }
}
