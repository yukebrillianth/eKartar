<?php

namespace App\Filament\Resources\ContributionResource\Pages;

use App\Exports\WithdrawlsExport;
use App\Filament\Resources\ContributionResource;
use App\Filament\Resources\ContributionResource\Widgets\ContributionAlertBox;
use App\Filament\Resources\ContributionResource\Widgets\ContributionDetailOverview;
use App\Models\Contribution;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\IconPosition;
use Maatwebsite\Excel\Facades\Excel;

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
                    ->authorize(auth()->user()->roles[0]->name !== 'karang_taruna' && count(auth()->user()->roles->toArray()) === 1),
                Action::make('export')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Export Jimpitan')
                    ->modalSubmitActionLabel('Export sekarang')
                    ->form([
                        Select::make('encrypt')
                            ->label('Enkripsi File')
                            ->options([
                                false => 'Tidak ada',
                                true => 'Password'
                            ])
                            ->required()
                            ->live(),
                        TextInput::make('password')
                            ->hidden(fn (Get $get): bool => !$get('encrypt'))
                            ->required(fn (Get $get): bool => filled($get('encrypt')))
                            ->label('Password')
                            ->password()
                            ->revealable()
                    ])
                    ->hidden(function (Contribution $record) {
                        return !$record->withdrawls->count() || $record->is_anounced ||  !$record->is_calculation_complete;
                    })
                    ->authorize(auth()->user()->roles[0]->name !== 'karang_taruna' && count(auth()->user()->roles->toArray()) === 1)
                    ->action(fn (array $data) => Excel::download(new WithdrawlsExport($this->getRecord()->id, $this->getRecord()->date, $data['encrypt'], $data['password'] ?? null), "eKartar-jimpitan-{$this->getRecord()->date}.xlsx")),
            ])->label('Lainnya')
                ->icon('heroicon-m-ellipsis-vertical')
                ->iconPosition(IconPosition::After)
                ->color('gray')
                ->button()
        ];
    }

    protected function getHeaderWidgets(): array
    {
        if ($this->getRecord()->trashed()) {
            return [
                ContributionAlertBox::class,
                ContributionDetailOverview::class,
            ];
        } else {
            return [
                ContributionDetailOverview::class,
            ];
        }
    }
}
