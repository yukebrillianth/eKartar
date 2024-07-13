<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Traits\TransactionableTrait;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Contribution extends Model
{
    use HasFactory, SoftDeletes, HasUuids, TransactionableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'created_by',
        'image_path',
        'is_done',
        'is_calculation_complete',
    ];

    /**
     * UUID
     *
     * @var string
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Holds the methods' names of Eloquent Relations 
     * to fall on delete cascade or on restoring
     * 
     * @var array
     */
    protected static $relations_to_cascade = [];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($resource) {
            foreach (static::$relations_to_cascade as $relation) {
                foreach ($resource->{$relation}()->get() as $item) {
                    $item->delete();
                }
            }
        });

        static::restoring(function ($resource) {
            foreach (static::$relations_to_cascade as $relation) {
                foreach ($resource->{$relation}()->withTrashed()->get() as $item) {
                    $item->restore();
                }
            }
        });
    }

    public static function booted()
    {
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    // Mark contribution as calculation complete
    public function completeCalc()
    {
        if (!$this->is_calculation_complete) {
            DB::beginTransaction();
            try {
                // Buat transaksi baru
                $transaction = new Transaction();
                $transaction->type = TransactionType::Debit;
                $transaction->value = $this->withdrawls()->sum('value');
                $transaction->title = "Penyelesaian Jimpitan " . $this->date;

                // Simpan transaksi
                $transaction->transactionable()->associate($this);
                $transaction->save();

                // Buat catatan saldo baru
                $this->updateBalance($transaction);

                // Update status jimpitan
                $this->update([
                    'is_calculation_complete' => true
                ]);

                DB::commit();
                // Kirim notifikasi sukses
                Notification::make()
                    ->title("Berhasil menyelesaikan jimpitan " . $this->date)
                    ->body('Transaksi telah ditambahkan.')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                DB::rollback();

                // Kirim notifikasi gagal
                Notification::make()
                    ->title("Gagal menambahkan jimpitan " . $this->date)
                    ->body('Transaksi gagal ditambahkan.')
                    ->danger()
                    ->send();
                Log::error('Transaction processing failed: ' . $e->getMessage(), ['exception' => $e]);
            }
        } else {
            Notification::make()
                ->title("Jimpitan Sudah Diselesaikan!")
                ->body('Transaksi gagal ditambahkan.')
                ->danger()
                ->send();
        }
    }

    // Mark contribution as calculation complete
    public function cancelCalc()
    {
        if ($this->is_calculation_complete) {
            DB::beginTransaction();
            try {
                // Buat transaksi baru
                $transaction = new Transaction();
                $transaction->type = TransactionType::Credit;
                $transaction->value = $this->withdrawls()->sum('value');
                $transaction->title = "Pembatalan Jimpitan " . $this->date;

                // Simpan transaksi
                $transaction->transactionable()->associate($this);
                $transaction->save();

                // Buat catatan saldo baru
                $this->updateBalance(transaction: $transaction);

                // Update status jimpitan
                $this->update([
                    'is_calculation_complete' => false
                ]);

                DB::commit();

                // Kirim notifikasi sukses
                Notification::make()
                    ->title("Berhasil membatalkan jimpitan " . $this->id)
                    ->body('Transaksi telah dibatalkan.')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                DB::rollback();

                // Kirim notifikasi gagal
                Notification::make()
                    ->title("Gagal membatalkan jimpitan " . $this->id)
                    ->body('Transaksi gagal dibatalkan.')
                    ->danger()
                    ->send();
                Log::error('Transaction processing failed: ' . $e->getMessage(), ['exception' => $e]);
            }
        }
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Mendapatkan semua data nilai penarikan per rumah
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function withdrawls(): HasMany
    {
        return $this->hasMany(Withdrawl::class);
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'transactionable');
    }
}
