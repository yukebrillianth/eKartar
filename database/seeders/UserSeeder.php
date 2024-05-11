<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Yuke Brilliant Hestiavin',
            'email' => 'yuke@ekartar.my.id',
            'phone' => '085755773985',
            'password' => Hash::make('ekartar')
        ]);

        $user->assignRole('super_admin');
    }
}
