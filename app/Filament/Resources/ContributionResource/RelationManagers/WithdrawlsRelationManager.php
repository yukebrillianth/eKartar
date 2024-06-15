<?php

namespace App\Filament\Resources\ContributionResource\RelationManagers;

use App\Models\House;
use App\Models\Withdrawl;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
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
                    ->label('Rumah')
                    ->relationship('house', 'name', function (Builder $query, string $operation) use ($contributionId) {
                        if ($operation === 'create') {
                            $query->where('is_active', true)
                                ->whereDoesntHave('withdrawls', function ($subQuery) use ($contributionId) {
                                    $subQuery->where('contribution_id', $contributionId)
                                        ->whereNull('deleted_at');
                                })
                                ->orderBy('name', 'ASC');
                        } else {
                            $query;
                        }
                    })
                    // ->rules([
                    //     fn (): Closure => function (string $attribute, $value, Closure $fail) {
                    //         $alredyExist = Withdrawl::where('contribution_id', $this->getOwnerRecord()->id)
                    //             ->where('house_id', $value)->count();
                    //         if ($alredyExist) {
                    //             $fail("Rumah sudah diperiksa");
                    //         }
                    //     },
                    // ])
                    ->getOptionLabelFromRecordUsing(fn (House $record) => "{$record->name} ({$record->holder})")
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Toggle::make('is_rapel')
                    ->label('Sudah Merapel')
                    ->default(false)
                    ->inline(false),
                Forms\Components\TextInput::make('value')
                    ->label('Jumlah')
                    // ->required()
                    ->prefix('Rp')
                    ->mask(RawJs::make('$money($input, \',\')'))
                    ->stripCharacters('.')
                    ->numeric()
                // ->live()
                // ->afterStateUpdated(function ($state, Set $set, string $operation) {
                //     if (blank($state)) return;
                //     if ($operation === 'create') {
                //         $set('is_contribute', true);
                //     }
                // }),
                // Forms\Components\Toggle::make('is_contribute')
                //     ->label('Mengisi atau tidak?')
                //     ->live()
                //     ->afterStateUpdated(function ($state, Set $set, string $operation) {
                //         if (blank($state)) return;
                //         $set('value', 0);
                //     }),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Penarikan')
            ->description('Data Penarikan jimpitan.')
            ->emptyStateDescription('Tambahkan penarikan untuk memulai.')
            ->recordTitleAttribute('house.name')
            ->poll('5s')
            ->deferLoading()
            ->striped()
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
                    ->getStateUsing(function (Model $record): string {

                        return $record->is_rapel ? '2' : ($record->is_contribute ? '1' : '0');
                    })
                    ->color(fn (string $state): string => match ($state) {
                        '2' => 'warning',
                        '1' => 'success',
                        '0' => 'danger',
                        '' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => $state === '1' ? 'Mengisi' : ($state === '2' ? 'Sudah Rapel' : 'Kosong'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Jumlah')
                    ->money('idr')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Diperiksa oleh')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('house.name', 'asc')
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambahkan')
                    ->authorize(!$this->getOwnerRecord()->is_calculation_complete)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        $data['contribution_id'] = $this->getOwnerRecord()->id;
                        if ($data['value']) {
                            $data['is_contribute'] = true;
                        } else {
                            $data['value'] = 0;
                            $data['is_contribute'] = false;
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->authorize(function () {
                        if (!$this->getOwnerRecord()->is_calculation_complete) {
                            return auth()->user()->roles[0] !== "karang_taruna" && count(auth()->user()->roles->toArray()) === 1;
                        }
                        return false;
                    }),
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
