<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Traits\TransactionableTrait;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Expense extends Model
{
    use HasFactory, SoftDeletes, HasUuids, TransactionableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'value',
        'date',
        'user_id',
        'image_path',
    ];

    /**
     * UUID
     *
     * @var string
     */
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::created(function (Model $model) {
            DB::beginTransaction();
            try {
                $transaction = new Transaction();
                $transaction->type = TransactionType::Credit;
                $transaction->value = $model->value;

                $transaction->title = "Pengeluaran " . $model->date;

                $transaction->transactionable()->associate($model);
                $transaction->save();

                $model->updateBalance($transaction, value: $model->value);

                DB::commit();
                Notification::make()
                    ->title("Berhasil menyelesaikan pengeluaran " . $model->date)
                    ->body('Transaksi telah ditambahkan.')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                DB::rollback();
                Notification::make()
                    ->title("Gagal menambahkan pengeluaran " . $model->date)
                    ->body('Transaksi gagal ditambahkan.')
                    ->danger()
                    ->send();
                Log::error('Expense Transaction processing failed: ' . $e->getMessage(), ['exception' => $e]);
                throw $e;
            }
        });

        static::deleted(function (Model $model) {
            DB::beginTransaction();
            try {
                $transaction = new Transaction();
                $transaction->type = TransactionType::Debit;
                $transaction->value = $model->value;

                $transaction->title = "Pembatalan Pengeluaran " . $model->date;

                $transaction->transactionable()->associate($model);
                $transaction->save();

                $model->updateBalance($transaction, value: $model->value);

                DB::commit();
                Notification::make()
                    ->title("Berhasil menghapus pengeluaran " . $model->date)
                    ->body('Transaksi telah dihapus.')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                DB::rollback();
                Notification::make()
                    ->title("Gagal menghapus pengeluaran " . $model->date)
                    ->body('Transaksi gagal dihapus.')
                    ->danger()
                    ->send();
                Log::error('Expense Transaction deletion failed: ' . $e->getMessage(), ['exception' => $e]);
                throw $e;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
