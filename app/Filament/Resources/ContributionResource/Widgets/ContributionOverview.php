<?php

namespace App\Filament\Resources\ContributionResource\Widgets;

use App\Models\Balance;
use App\Models\Expense;
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
        $startDate = Carbon::now()->subMonths(6)->startOfMonth(); // Mulai dari 6 bulan yang lalu
        $endDate = Carbon::now()->startOfMonth(); // Sampai bulan ini
        $totalMonth = [];
        $totalExpenseMonth = [];

        while ($startDate->lessThanOrEqualTo($endDate)) {
            $startOfMonth = $startDate->copy()->startOfMonth(); // Awal bulan
            $endOfMonth = $startDate->copy()->endOfMonth(); // Akhir bulan

            $totalMonth[] = Withdrawl::with('contribution')->whereHas('contribution', function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('date', [$startOfMonth, $endOfMonth])->where('is_calculation_complete', true);
            })->sum('value');

            $totalExpenseMonth[] = Expense::with('contribution')->whereBetween('date', [$startOfMonth, $endOfMonth])->sum('value');

            $startDate->addMonth(); // Tambahkan 1 bulan
        }

        // Dapatkan tanggal awal bulan ini
        $startDateMonthly = Carbon::now()->startOfMonth();

        // Dapatkan tanggal akhir bulan ini
        $endDateMonthly = Carbon::now()->endOfMonth();

        $totalYear = [];
        $currentYear = Carbon::now()->year;

        for ($year = $currentYear - 4; $year <= $currentYear; $year++) {
            $startOfYear = Carbon::create($year, 1, 1)->startOfYear();
            $endOfYear = Carbon::create($year, 12, 31)->endOfYear();

            $yearlyContribution = Withdrawl::with('contribution')
                ->whereHas('contribution', function ($query) use ($startOfYear, $endOfYear) {
                    $query->whereBetween('date', [$startOfYear, $endOfYear])
                        ->where('is_calculation_complete', true);
                })
                ->sum('value');

            $totalYear[] = $yearlyContribution;
        }

        return [
            Stat::make('Saldo Keseluruhan', 'Rp ' . number_format(Balance::orderBy('id', 'desc')->latest()->pluck('value')->first(), 0, "", "."))
                ->description('Saldo terkumpul')
                ->extraAttributes([
                    'class' => 'dark',
                    'style' => "background: rgba(24,24,27,1); --tw-ring-color: hsla(0,0%,100%,.1);"
                ]),
            Stat::make('Perolehan Tahun Ini', 'Rp ' . number_format(Withdrawl::with('contribution')->whereHas('contribution', function ($query) use ($startDateMonthly, $endDateMonthly) {
                $query->whereBetween('date', [Carbon::now()->startOfYear(), Carbon::now()])->where('is_calculation_complete', true);
            })->sum('value'), 0, "", "."))
                ->description('Saldo terkumpul')
                ->chart($totalYear),
            Stat::make('Perolehan Bulan Ini', 'Rp ' . number_format(Withdrawl::with('contribution')->whereHas('contribution', function ($query) use ($startDateMonthly, $endDateMonthly) {
                $query->whereBetween('date', [$startDateMonthly, $endDateMonthly])->where('is_calculation_complete', true);
            })->sum('value'), 0, "", "."))
                ->description('Saldo terkumpul')
                ->chart($totalMonth),
            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format(Expense::whereBetween('date', [$startDateMonthly, $endDateMonthly])->sum('value'), 0, "", "."))
                ->description('Saldo digunakan')
                ->chart($totalExpenseMonth),
            // Stat::make('Total Keluarga', House::where('is_active', true)->count())
            //     ->description('Rumah Berpenghuni'),
        ];
    }
}
