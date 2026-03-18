<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:user {email} {password} {username?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Allow to create users such as admins by providing an email address, username and password';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $username = $this->argument('username');
        if ($username == null) {
            $username = $this->argument('email');
        }
        DB::table('users')->insert([
            'username' => $username,
            'email' => $this->argument('email'),
            'password' => Hash::make($this->argument('password')),
        ]);

        return Command::SUCCESS;
    }
}
