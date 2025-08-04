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
        User::create(['name' => 'kantin', 'username' => 'kantin123', 'email' => 'kantin@gmail.com', 'password' => bcrypt('kantin12345'), 'role' => 'kantin',]);
        User::create(['name' => 'BC', 'username' => 'bc123', 'email' => 'bc@gmail.com', 'password' => bcrypt('bc12345'), 'role' => 'bc',]);
    }
}
