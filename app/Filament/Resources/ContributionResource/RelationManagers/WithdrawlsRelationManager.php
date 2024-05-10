<?php

namespace App\Filament\Resources\ContributionResource\RelationManagers;

use App\Models\House;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WithdrawlsRelationManager extends RelationManager
{
    protected static string $relationship = 'withdrawls';

    public function form(Form $form): Form
    {
        $contributionId = $this->getOwnerRecord()->id;
        return $form
            ->schema([
                // Forms\Components\TextInput::make('contribution_id')
                //     ->label('id')
                //     ->default(function (RelationManager $livewire): string {
                //         return $livewire->getOwnerRecord()->pluck('id')[0];
                //     })
                //     ->readonly()
                //     ->required(),
                Forms\Components\Select::make('house_id')
                    ->relationship('house', 'name', function (Builder $query) use ($contributionId) {
                        $query->where('is_active', true)
                            ->whereNotExists(function ($subQuery) use ($contributionId) {
                                $subQuery->select('house_id')
                                    ->from('withdrawls')
                                    ->whereColumn('withdrawls.house_id', 'houses.id')
                                    ->where('withdrawls.contribution_id', $contributionId)
                                    ->where('withdrawls.deleted_at', null);
                            })
                            ->orderBy('name', 'ASC');
                    })
                    ->getOptionLabelFromRecordUsing(fn (House $record) => "{$record->name} ({$record->holder})")
                    ->label('Rumah')
                    ->required(),
                Forms\Components\TextInput::make('value')
                    ->label('Jumlah')
                    ->required()
                    ->prefix('Rp')
                    ->default(0)
                    ->numeric()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, string $operation) {
                        if (blank($state)) return;
                        if ($operation === 'create') {
                            $set('is_contribute', true);
                        }
                    }),
                Forms\Components\Toggle::make('is_contribute')
                    ->label('Mengisi atau tidak?')
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, string $operation) {
                        if (blank($state)) return;
                        $set('value', 0);
                    }),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Penarikan')
            ->recordTitleAttribute('house.name')
            ->poll('5s')
            ->columns([
                Tables\Columns\TextColumn::make('house.name')
                    ->label('Rumah')
                    ->getStateUsing(function (Model $record): string {

                        return "{$record->house->name} ({$record->house->holder})";
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_contribute')
                    ->badge()
                    ->label('Status')
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'danger',
                        '' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => $state === '1' ? 'Mengisi' : 'Kosong')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Jumlah')
                    ->money('idr')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengambil')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        $data['contribution_id'] = $this->getOwnerRecord()->id;
                        if ($data['value']) {
                            $data['is_contribute'] = true;
                        } else {
                            $data['is_contribute'] = false;
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
