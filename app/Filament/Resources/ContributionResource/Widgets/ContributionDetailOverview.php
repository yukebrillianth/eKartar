<?php

namespace App\Filament\Resources\ContributionResource\Widgets;

use App\Models\Contribution;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class ContributionDetailOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '5s';

    protected static bool $isLazy = false;

    public ?Model $record = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Saldo', 'Rp ' . number_format(Contribution::find($this->record->id)->withdrawls->sum('value'), 0, "", "."))
                ->description('Saldo terkumpul'),
            Stat::make('Total Rumah Terisi', Contribution::find($this->record->id)->withdrawls->where('is_contribute', true)->count())
                ->description('Rumah mengisi jimpitan'),
            Stat::make('Total Rumah Kosong', Contribution::find($this->record->id)->withdrawls->where('is_contribute', false)->count())
                ->description('Rumah tidak mengisi jimpitan'),
        ];
    }
}
