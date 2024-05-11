<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('houses')->insert([
            [
                'name' => 'Yuke Brilliant Hestiavin',
                'email' => 'yuke@ekartar.my.id',
                'phone' => '085755773985',
                'password' => Hash::make('ekartar')
            ]
        ]);
    }
}
