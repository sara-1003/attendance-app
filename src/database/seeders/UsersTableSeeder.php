<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->count(5)->create();

        User::factory()->admin()->create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('12345678'),
        ]);
    }
}
