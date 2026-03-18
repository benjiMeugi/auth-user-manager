<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateCrudPermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:permission {ability} {app} {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ability = $this->argument('ability');
        $app = $this->argument('app');
        DB::table('permissions')->insert([
            ['label' => $ability . '-list', 'display_label' => $ability . '-list', 'app' => $app],
            ['label' => $ability . '-create', 'display_label' => $ability . '-create', 'app' => $app],
            ['label' => $ability . '-update', 'display_label' => $ability . '-update', 'app' => $app],
            ['label' => $ability . '-delete', 'display_label' => $ability . '-delete', 'app' => $app],
        ]);

        $username = $this->argument('username');
        if ($username) {
            $user = DB::table('users')->where('username', '=', $username)->first();
            $permissions = DB::table('permissions')->where('app', '=', $app)->get();
            foreach ($permissions as $permission) {
                DB::table('permission_users')->insert(['user_id' => $user->id, 'permission_id' => $permission->id]);
            }
        }
        return Command::SUCCESS;
    }
}
