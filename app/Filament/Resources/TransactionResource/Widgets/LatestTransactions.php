<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LatestTransactions extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Semua Transaksi')
            ->description('Data transaksi tahun ini.')
            ->emptyStateDescription('Selesaikan jimpitan atau pengeluaran untuk memulai.')
            ->recordTitleAttribute('title')
            ->poll('5s')
            ->deferLoading()
            ->striped()
            ->query(
                Transaction::whereYear('created_at', date('Y'))
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Transaksi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->label('Tipe')
                    ->getStateUsing(function (Model $record): TransactionType {
                        return $record->type;
                    })
                    ->color(fn (TransactionType $state): string => match ($state) {
                        TransactionType::Debit => 'success',
                        TransactionType::Credit => 'danger',
                    })
                    ->formatStateUsing(fn (TransactionType $state): string => $state === TransactionType::Debit ? 'Debit' : 'Kredit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('value')
                    ->label('Jumlah')
                    ->money('idr')
                    ->sortable(),
                TextColumn::make('balance.value')
                    ->label('Saldo Akhir')
                    ->money('idr')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('Mulai dari')->minDate(now()->subYear()),
                        DatePicker::make('created_until')->label('Hingga')->maxDate(now()),
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
                SelectFilter::make('type')
                    // ->multiple()
                    ->options([
                        'debit' => 'Debit',
                        'credit' => 'Credit'
                    ])
            ]);
    }
}
