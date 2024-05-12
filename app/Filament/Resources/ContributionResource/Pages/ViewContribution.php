<?php

namespace App\Filament\Resources\ContributionResource\Pages;

use App\Filament\Resources\ContributionResource;
use App\Filament\Resources\ContributionResource\Widgets\ContributionDetailOverview;
use App\Models\Contribution;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\IconPosition;
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
            ActionGroup::make([
                Action::make('finish')
                    // ->label(function (Contribution $record) {
                    //     if ($record->is_calculation_complete) {
                    //         return 'Penarikan Diselesaikan';
                    //     } else {
                    //         return 'Selesaikan';
                    //     }
                    // })
                    ->label('Selesaikan')
                    // ->icon(function (Contribution $record) {
                    //     if ($record->is_calculation_complete) {
                    //         return 'heroicon-o-check-badge';
                    //     }
                    // })
                    ->icon('heroicon-o-check-badge')
                    // ->button()
                    ->action(fn (Contribution $record) => $record->completeCalc())
                    ->color('success')
                    ->hidden(function (Contribution $record) {
                        return !$record->withdrawls->count() || $record->is_calculation_complete;
                    }),
                // ->disabled(function (Contribution $record) {
                //     return $record->is_calculation_complete;
                // }),
                Action::make('cancel')
                    // ->label(function (Contribution $record) {
                    //     if ($record->is_calculation_complete) {
                    //         return 'Penarikan Diselesaikan';
                    //     } else {
                    //         return 'Selesaikan';
                    //     }
                    // })
                    ->label('Batalkan')
                    // ->icon(function (Contribution $record) {
                    //     if ($record->is_calculation_complete) {
                    //         return 'heroicon-o-check-badge';
                    //     }
                    // })
                    ->icon('heroicon-o-x-mark')
                    // ->button()
                    ->action(fn (Contribution $record) => $record->cancelCalc())
                    ->color('danger')
                    ->hidden(function (Contribution $record) {
                        return !$record->withdrawls->count() || !$record->is_calculation_complete;
                    }),
                Action::make('Umumkan')
                    ->icon('heroicon-o-paper-airplane')
                    // ->button()
                    ->color('info')
                    ->hidden(function (Contribution $record) {
                        return !$record->withdrawls->count() || $record->is_anounced ||  !$record->is_calculation_complete;
                    })
            ])->label('Lainnya')
                ->icon('heroicon-m-ellipsis-vertical')
                ->iconPosition(IconPosition::After)
                ->color('gray')
                ->button()
                ->authorize(auth()->user()->roles[0]->name !== 'karang_taruna' && count(auth()->user()->roles->toArray()) === 1)
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ContributionDetailOverview::class,
        ];
    }
}
