<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContributionResource\Pages;
use App\Filament\Resources\ContributionResource\RelationManagers;
use App\Filament\Resources\ContributionResource\RelationManagers\WithdrawlsRelationManager;
use App\Models\Contribution;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class ContributionResource extends Resource
{
    protected static ?string $model = Contribution::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';

    protected static ?string $modelLabel = 'Jimpitan';

    protected static ?string $pluralModelLabel  = 'Semua Jimpitan';

    protected static ?string $navigationLabel = 'Jimpitan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Jimpitan')
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Jimpitan')
                            ->default(Carbon::today())
                            ->required(),
                        Forms\Components\Select::make('users')
                            ->multiple()
                            ->preload()
                            ->relationship('users', 'name')
                            ->label('Dilaksanakan Oleh')
                            ->required(),
                        Forms\Components\FileUpload::make('image_path')
                            ->image()
                            ->disk('r2')
                            ->directory('contribution')
                            ->visibility('public')
                            ->visibleOn(['edit', 'view'])
                            ->label('Gambar Proses Penghitungan')
                            ->hidden(function (Contribution $record) {
                                return !$record->is_calculation_complete;
                            })
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Semua data jimpitan')
            ->emptyStateDescription('Tambahkan jimpitan untuk memulai.')
            ->poll('5s')
            ->deferLoading()
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal Jimpitan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Pelaksana')
                    ->searchable(),
                Tables\Columns\TextColumn::make('withdrawls_sum_value')
                    ->sum('withdrawls', 'value')
                    ->label('Jumlah')
                    ->money('idr')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('withdrawls_count')
                    ->counts(['withdrawls' => fn (Builder $query) => $query->where('is_contribute', true)])
                    ->label('Rumah Terisi')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_calculation_complete')
                    ->label('Selesai')
                    ->icon(fn (string $state): string => match ($state) {
                        '1' => 'heroicon-o-check-circle',
                        '' => 'heroicon-o-x-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        '' => 'danger',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->after(function (Contribution $record) {
                        if ($record->image_path) {
                            Storage::disk('r2')->delete($record->image_path);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            WithdrawlsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContributions::route('/'),
            'create' => Pages\CreateContribution::route('/create'),
            'view' => Pages\ViewContribution::route('/{record}'),
            'edit' => Pages\EditContribution::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
