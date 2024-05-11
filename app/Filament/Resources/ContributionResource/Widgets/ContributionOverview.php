<?php

namespace App\Filament\Resources\ContributionResource\Widgets;

use App\Models\House;
use App\Models\Withdrawl;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContributionOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '5s';

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // $startDate = Carbon::now()->subDays(6); // Menggunakan Carbon untuk menghasilkan tanggal 7 hari yang lalu
        // $total = [];

        // for ($i = 0; $i < 7; $i++) {
        //     $currentDate = $startDate->copy()->addDays($i); // Tambahkan $i hari ke tanggal awal
        //     $formattedDate = $currentDate->toDateString(); // Format tanggal untuk query

        //     $total[] = Withdrawl::whereDate('date', $formattedDate)
        //         ->sum('price');
        // }

        $startDate = Carbon::now()->subMonths(6)->startOfMonth(); // Mulai dari 6 bulan yang lalu
        $endDate = Carbon::now()->startOfMonth(); // Sampai bulan ini
        $totalMonth = [];

        while ($startDate->lessThanOrEqualTo($endDate)) {
            $startOfMonth = $startDate->copy()->startOfMonth(); // Awal bulan
            $endOfMonth = $startDate->copy()->endOfMonth(); // Akhir bulan

            $totalMonth[] = Withdrawl::with('contribution')->whereHas('contribution', function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('date', [$startOfMonth, $endOfMonth]);
            })->sum('value');

            $startDate->addMonth(); // Tambahkan 1 bulan
        }

        // Dapatkan tanggal awal bulan ini
        $startDateMonthly = Carbon::now()->startOfMonth();

        // Dapatkan tanggal akhir bulan ini
        $endDateMonthly = Carbon::now()->endOfMonth();

        return [
            Stat::make('Saldo Bulan Ini', 'Rp ' . number_format(Withdrawl::with('contribution')->whereHas('contribution', function ($query) use ($startDateMonthly, $endDateMonthly) {
                $query->whereBetween('date', [$startDateMonthly, $endDateMonthly]);
            })->sum('value'), 0, "", "."))
                ->description('Saldo terkumpul')
                ->chart($totalMonth),
            Stat::make('Total Keluarga', House::where('is_active', true)->count())
                ->description('Rumah Berpenghuni'),
        ];
    }
}
