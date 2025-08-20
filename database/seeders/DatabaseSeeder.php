<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['name' => 'admin', 'username' => 'admin22', 'email' => 'admin@gmail.com', 'password' => bcrypt('admin12345'), 'role' => 'admin',]);
        User::create(['name' => 'bank', 'username' => 'bank123', 'email' => 'bank@gmail.com', 'password' => bcrypt('bank12345'), 'role' => 'bank',]);
        User::create(['name' => 'canteen', 'username' => 'canteen123', 'email' => 'canteen@gmail.com', 'password' => bcrypt('canteen12345'), 'role' => 'canteen',]);
        User::create(['name' => 'bc', 'username' => 'bc123', 'email' => 'bc@gmail.com', 'password' => bcrypt('bc12345'), 'role' => 'bc',]);
    }
}
