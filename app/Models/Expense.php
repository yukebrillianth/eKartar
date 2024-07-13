<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Traits\TransactionableTrait;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        'ref_id'
    ];

    /**
     * UUID
     *
     * @var string
     */
    protected $keyType = 'string';
    public $incrementing = false;

    public static function booted()
    {
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->ref_id = self::generateRefId();
        });

        static::created(function (Model $model) {
            DB::beginTransaction();
            try {
                // Buat transaksi baru
                $transaction = new Transaction();
                $transaction->type = TransactionType::Credit;
                $transaction->value = $model->value;

                // Simpan transaksi
                if ($model->user->email === config('app.system_automation_email')) {
                    $transaction->title = $model->title;
                } else {
                    $transaction->title = "Pengeluaran " . $model->ref_id;
                }
                $transaction->transactionable()->associate($model);
                $transaction->save();

                // Buat catatan saldo baru
                $model->updateBalance($transaction);

                DB::commit();

                // Kirim notifikasi sukses
                Notification::make()
                    ->title("Berhasil menyelesaikan pengeluaran " . $model->date)
                    ->body('Transaksi telah ditambahkan.')
                    ->success()
                    ->send();
            } catch (\Throwable $e) {
                DB::rollback();

                // Kirim notifikasi gagal
                Notification::make()
                    ->title("Gagal menambahkan pengeluaran " . $model->date)
                    ->body('Transaksi gagal ditambahkan.')
                    ->danger()
                    ->send();

                Log::error('Expense Transaction processing failed: ' . $e->getMessage(), ['exception' => $e]);
                \Sentry\captureMessage('Transaction processing failed: ' . $e->getMessage());
                \Sentry\captureException($e);
            }
        });

        static::deleted(function (Model $model) {
            DB::beginTransaction();
            try {
                // Buat transaksi baru
                $transaction = new Transaction();
                $transaction->type = TransactionType::Debit;
                $transaction->value = $model->value;

                // Simpan transaksi
                $transaction->title = "Pembatalan Pengeluaran " . $model->date;
                $transaction->transactionable()->associate($model);
                $transaction->save();

                // Buat catatan saldo baru
                $model->updateBalance($transaction);

                DB::commit();

                // Kirim notifikasi sukses
                Notification::make()
                    ->title("Berhasil menghapus pengeluaran " . $model->date)
                    ->body('Transaksi telah dihapus.')
                    ->success()
                    ->send();
            } catch (\Throwable $e) {
                DB::rollback();

                // Kirim notifikasi gagal
                Notification::make()
                    ->title("Gagal menghapus pengeluaran " . $model->date)
                    ->body('Transaksi gagal dihapus.')
                    ->danger()
                    ->send();
                Log::error('Expense Transaction deletion failed: ' . $e->getMessage(), ['exception' => $e]);
                \Sentry\captureMessage('Transaction processing failed: ' . $e->getMessage());
                \Sentry\captureException($e);
            }
        });
    }

    public static function generateRefId()
    {
        $year = date('Y');
        $month = date('m');
        $count = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;

        return $year . $month . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'transactionable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
