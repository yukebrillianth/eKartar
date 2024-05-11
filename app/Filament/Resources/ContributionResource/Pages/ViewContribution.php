<?php

namespace App\Filament\Resources\ContributionResource\Pages;

use App\Filament\Resources\ContributionResource;
use App\Filament\Resources\ContributionResource\Widgets\ContributionDetailOverview;
use App\Models\Contribution;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewContribution extends ViewRecord
{
    protected static string $resource = ContributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil-square')
                ->label('Ubah data'),
            Action::make('finish')
                ->label(function (Contribution $record) {
                    if ($record->is_calculation_complete) {
                        return 'Penarikan Diselesaikan';
                    } else {
                        return 'Selesaikan';
                    }
                })
                ->icon(function (Contribution $record) {
                    if ($record->is_calculation_complete) {
                        return 'heroicon-o-check-badge';
                    }
                })
                ->button()
                ->action(fn (Contribution $record) => $record->completeCalc())
                ->color('success')
                ->hidden(function (Contribution $record) {
                    return !$record->withdrawls->count();
                })
                ->disabled(function (Contribution $record) {
                    return $record->is_calculation_complete;
                }),
            Action::make('Umumkan')
                ->icon('heroicon-o-paper-airplane')
                ->button()
                ->color('info')
                ->hidden(function (Contribution $record) {
                    return !$record->withdrawls->count() || $record->is_anounced ||  !$record->is_calculation_complete;
                })
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ContributionDetailOverview::class,
        ];
    }
}
