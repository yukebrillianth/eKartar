<?php

namespace App\Filament\Resources\HouseResource\Widgets;

use App\Models\House;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HouseOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Keluarga', House::where('is_active', true)->count())
                ->description('Rumah Berpenghuni'),
        ];
    }
}
