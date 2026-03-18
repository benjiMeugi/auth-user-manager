<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // php artisan create:user admin@example.com 123456 admin
        // php artisan create:permission role employe admin
        // php artisan create:permission permission employe admin
        // php artisan create:permission employe employe admin

        // DB::table('users')-> insert([
        //     'username' => 'admin',
        //     'email' => 'admin@example.com',
        //     'password' => Hash::make('123456'),
        // ]);

        // $ability = 'permission';
        // DB::table('permissions')->insert([
        //     ['label' => $ability.'-list', 'display_label' => $ability.'-list'],
        //     ['label' => $ability.'-create', 'display_label' => $ability.'-create'],
        //     ['label' => $ability.'-update', 'display_label' => $ability.'-update'],
        //     ['label' => $ability.'-delete', 'display_label' => $ability.'-delete'],
        // ]);
        // $ability = 'role';
        // DB::table('roles')->insert([
        //     ['label' => $ability.'-list', 'display_label' => $ability.'-list'],
        //     ['label' => $ability.'-create', 'display_label' => $ability.'-create'],
        //     ['label' => $ability.'-update', 'display_label' => $ability.'-update'],
        //     ['label' => $ability.'-delete', 'display_label' => $ability.'-delete'],
        // ]);
        // $ability = 'user';
        // DB::table('users')->insert([
        //     ['label' => $ability.'-list', 'display_label' => $ability.'-list'],
        //     ['label' => $ability.'-create', 'display_label' => $ability.'-create'],
        //     ['label' => $ability.'-update', 'display_label' => $ability.'-update'],
        //     ['label' => $ability.'-delete', 'display_label' => $ability.'-delete'],
        // ]);
    }
}
