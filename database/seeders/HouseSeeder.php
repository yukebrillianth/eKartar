<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('houses')->insert([
            ['name' => 'AH-01', 'holder' => 'Bp. Lius', 'is_active' => true],
            ['name' => 'AH-02 & AH-03', 'holder' => 'Ibu Maria', 'is_active' => true],
            ['name' => 'AH-04', 'holder' => 'Bp. Rudi', 'is_active' => true],
            ['name' => 'AH-05', 'holder' => 'Mas Siam', 'is_active' => true],
            ['name' => 'AH-06', 'holder' => 'Ibu Rini', 'is_active' => true],
            ['name' => 'AH-07', 'holder' => 'Ibu Tiara', 'is_active' => true],
            ['name' => 'AH-08', 'holder' => 'Bp. Sunarto', 'is_active' => true],
            ['name' => 'AH-09', 'holder' => 'Bp. Triantono', 'is_active' => true],
            ['name' => 'AH-10', 'holder' => 'Bp. Suyono', 'is_active' => true],
            ['name' => 'AH-11', 'holder' => 'Bp. Munardi', 'is_active' => true],
            ['name' => 'AH-12', 'holder' => 'Bp. Kris Rinata', 'is_active' => true],
            ['name' => 'AH-17', 'holder' => 'Bp. Aditya', 'is_active' => true],
            ['name' => 'AH-18', 'holder' => 'Bp. Parno', 'is_active' => true],
            ['name' => 'AH-19', 'holder' => 'Bp. Dwi Santoso', 'is_active' => true],
            ['name' => 'AH-20', 'holder' => 'Bp. Suroyo', 'is_active' => true],
            ['name' => 'AH-21', 'holder' => 'Ibu Decy', 'is_active' => true],
            ['name' => 'AH-22', 'holder' => 'Bp. Zaufi Fatkah', 'is_active' => true],
            ['name' => 'AH-23', 'holder' => 'Bp. Gansar', 'is_active' => true],
            ['name' => 'AH-24 A', 'holder' => 'Bp. Abdullah Izzin', 'is_active' => true],
            ['name' => 'AH-24 B', 'holder' => 'Bp. Diyan Taufiek', 'is_active' => true],
            ['name' => 'AH-25 & AH-26', 'holder' => 'Bp. Supoyo', 'is_active' => true],
            ['name' => 'AH-27', 'holder' => 'Bp. Sundoro', 'is_active' => true],
            ['name' => 'AH-28', 'holder' => 'Bp. Norman', 'is_active' => true],
            ['name' => 'AH-29', 'holder' => 'Bp. Afif Amrullah', 'is_active' => true],
            ['name' => 'AH-30', 'holder' => 'Bp. ??????', 'is_active' => true],
            ['name' => 'AH-31', 'holder' => 'Bp. Sunardi', 'is_active' => true],
            ['name' => 'AH-32', 'holder' => 'Bp. Syahriel Ahmad', 'is_active' => true],
            ['name' => 'AH-33', 'holder' => 'Bp. Agustinus', 'is_active' => true],
            ['name' => 'AH-34', 'holder' => 'Bp. Sunarto', 'is_active' => true],
            ['name' => 'AH-35', 'holder' => 'Bp. Agus Pujiono', 'is_active' => true],
            ['name' => 'AH-36', 'holder' => 'Ibu Iman', 'is_active' => true],
            ['name' => 'AH-37', 'holder' => 'Bp. Heri', 'is_active' => true],
            ['name' => 'AH-38', 'holder' => 'Bp. Ghozali', 'is_active' => true],
            ['name' => 'AH-39', 'holder' => 'Ibu Syaiful', 'is_active' => true],
            ['name' => 'AH-40', 'holder' => 'Bp. Gunawan', 'is_active' => true],
            ['name' => 'AH-41', 'holder' => 'Bp. Danu', 'is_active' => true],
            ['name' => 'AH-42', 'holder' => 'Ibu Widy', 'is_active' => true],
            ['name' => 'AH-43', 'holder' => 'Ibu Noorhasanah', 'is_active' => true],
            ['name' => 'AH-44', 'holder' => 'Bp. Dasir', 'is_active' => true],
            ['name' => 'AH-45', 'holder' => 'Bp. Rizky/P. Sukat', 'is_active' => true],
            ['name' => 'AH-46', 'holder' => 'Bp. Rimba', 'is_active' => true],
            ['name' => 'AH-47 & AH-48', 'holder' => 'Bp. Arif Marsudi', 'is_active' => true],
            ['name' => 'AH-49', 'holder' => 'Bp. Sugiyono', 'is_active' => true],
            ['name' => 'AF-01', 'holder' => 'Bp. Ryan', 'is_active' => true],
            ['name' => 'AF-02', 'holder' => 'Bp. Patmono', 'is_active' => true],
            ['name' => 'AF-03', 'holder' => 'Bp. Hasan', 'is_active' => true],
            ['name' => 'AF-04', 'holder' => 'Bp. Haryono', 'is_active' => true],
            ['name' => 'AF-05', 'holder' => 'Bp. Ilcham', 'is_active' => true],
            ['name' => 'AF-06', 'holder' => 'Bp. Rinanda', 'is_active' => true],
            ['name' => 'AF-07', 'holder' => 'Bp. Arif Tohari', 'is_active' => true],
            ['name' => 'AF-09', 'holder' => 'Bp. Irwan', 'is_active' => true],
            ['name' => 'AF-10', 'holder' => 'Bp. Dedi', 'is_active' => true],
            ['name' => 'AF-11', 'holder' => 'Bp. Abang', 'is_active' => true],
            ['name' => 'AF-12', 'holder' => 'Bp. Erik (Kantor)', 'is_active' => false],
            ['name' => 'AF-14', 'holder' => 'Bp. Kiemas Alvin', 'is_active' => true],
            ['name' => 'AF-15', 'holder' => 'Bp. Bandung', 'is_active' => true],
            ['name' => 'AF-16', 'holder' => 'Ibu Yoyok', 'is_active' => true],
            ['name' => 'AF-17', 'holder' => 'Bp. Fauzi', 'is_active' => true],
            ['name' => 'AF-18', 'holder' => 'Bp. Widodo', 'is_active' => true],
            ['name' => 'AF-19', 'holder' => 'Bp. Fery', 'is_active' => true],
            ['name' => 'AF-20', 'holder' => 'Ibu Yuli', 'is_active' => true]
        ]);
    }
}
