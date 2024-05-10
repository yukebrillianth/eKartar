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
            ['name' => 'AH-01', 'holder' => 'Bp. Lius'],
            ['name' => 'AH-02 & AH-03', 'holder' => 'Ibu Maria'],
            ['name' => 'AH-04', 'holder' => 'Bp. Rudi'],
            ['name' => 'AH-05', 'holder' => 'Mas Siam'],
            ['name' => 'AH-06', 'holder' => 'Ibu Rini'],
            ['name' => 'AH-07', 'holder' => 'Ibu Tiara'],
            ['name' => 'AH-08', 'holder' => 'Bp. Sunarto'],
            ['name' => 'AH-09', 'holder' => 'Bp. Triantono'],
            ['name' => 'AH-10', 'holder' => 'Bp. Suyono'],
            ['name' => 'AH-11', 'holder' => 'Bp. Munardi'],
            ['name' => 'AH-12', 'holder' => 'Bp. Kris Rinata'],
            ['name' => 'AH-17', 'holder' => 'Bp. Aditya'],
            ['name' => 'AH-18', 'holder' => 'Bp. Parno'],
            ['name' => 'AH-19', 'holder' => 'Bp. Dwi Santoso'],
            ['name' => 'AH-20', 'holder' => 'Bp. Suroyo'],
            ['name' => 'AH-21', 'holder' => 'Ibu Decy'],
            ['name' => 'AH-22', 'holder' => 'Bp. Zaufi Fatkah'],
            ['name' => 'AH-23', 'holder' => 'Bp. Gansar'],
            ['name' => 'AH-24 A', 'holder' => 'Bp. Abdullah Izzin'],
            ['name' => 'AH-24 B', 'holder' => 'Bp. Diyan Taufiek'],
            ['name' => 'AH-25 & AH-26', 'holder' => 'Bp. Supoyo'],
            ['name' => 'AH-27', 'holder' => 'Bp. Sundoro'],
            ['name' => 'AH-28', 'holder' => 'Bp. Norman'],
            ['name' => 'AH-29', 'holder' => 'Bp. Afif Amrullah'],
            ['name' => 'AH-30', 'holder' => 'Bp. ??????'],
            ['name' => 'AH-31', 'holder' => 'Bp. Sunardi'],
            ['name' => 'AH-32', 'holder' => 'Bp. Syahriel Ahmad'],
            ['name' => 'AH-33', 'holder' => 'Bp. Agustinus'],
            ['name' => 'AH-34', 'holder' => 'Bp. Sunarto'],
            ['name' => 'AH-35', 'holder' => 'Bp. Agus Pujiono'],
            ['name' => 'AH-36', 'holder' => 'Ibu Iman'],
            ['name' => 'AH-37', 'holder' => 'Bp. Heri'],
            ['name' => 'AH-38', 'holder' => 'Bp. Ghozali'],
            ['name' => 'AH-39', 'holder' => 'Ibu Syaiful'],
            ['name' => 'AH-40', 'holder' => 'Bp. Gunawan'],
            ['name' => 'AH-41', 'holder' => 'Bp. Danu'],
            ['name' => 'AH-42', 'holder' => 'Ibu Widy'],
            ['name' => 'AH-43', 'holder' => 'Ibu Noorhasanah'],
            ['name' => 'AH-44', 'holder' => 'Bp. Dasir'],
            ['name' => 'AH-45', 'holder' => 'Bp. Rizky/P. Sukat'],
            ['name' => 'AH-46', 'holder' => 'Bp. Rimba'],
            ['name' => 'AH-47 & AH-48', 'holder' => 'Bp. Arif Marsudi'],
            ['name' => 'AH-49', 'holder' => 'Bp. Sugiyono'],
            ['name' => 'AF-01', 'holder' => 'Bp. Ryan'],
            ['name' => 'AF-02', 'holder' => 'Bp. Patmono'],
            ['name' => 'AF-03', 'holder' => 'Bp. Hasan'],
            ['name' => 'AF-04', 'holder' => 'Bp. Haryono'],
            ['name' => 'AF-05', 'holder' => 'Bp. Ilcham'],
            ['name' => 'AF-06', 'holder' => 'Bp. Rinanda'],
            ['name' => 'AF-07', 'holder' => 'Bp. Arif Tohari'],
            ['name' => 'AF-09', 'holder' => 'Bp. Irwan'],
            ['name' => 'AF-10', 'holder' => 'Bp. Dedi'],
            ['name' => 'AF-11', 'holder' => 'Bp. Abang'],
            ['name' => 'AF-12', 'holder' => 'Bp. Erik (Kantor)', 'is_active' => false],
            ['name' => 'AF-14', 'holder' => 'Bp. Kiemas Alvin'],
            ['name' => 'AF-15', 'holder' => 'Bp. Bandung'],
            ['name' => 'AF-16', 'holder' => 'Ibu Yoyok'],
            ['name' => 'AF-17', 'holder' => 'Bp. Fauzi'],
            ['name' => 'AF-18', 'holder' => 'Bp. Widodo'],
            ['name' => 'AF-19', 'holder' => 'Bp. Fery'],
            ['name' => 'AF-20', 'holder' => 'Ibu Yuli']
        ]);
    }
}
