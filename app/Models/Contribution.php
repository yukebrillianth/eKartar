<?php

namespace App\Models;

use App\Enums\TransactionAction;
use App\Enums\TransactionType;
use App\Jobs\ProcessTransaction;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Contribution extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

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

    // mark contribution as calculation complete
    public function completeCalc(User $user)
    {
        if (!$this->is_calculation_complete) {
            DB::beginTransaction();
            try {
                $transaction = new Transaction();
                $transaction->type = TransactionType::Contribution;

                $transaction->title = "Jimpitan " . $this->date;

                $transaction->transactionable()->associate($this);
                $transaction->save();

                $this->updateBalance($transaction);


                $this->update([
                    'is_calculation_complete' => true
                ]);
                DB::commit();
                Notification::make()
                    ->title("Berhasil menyelesaikan jimpitan " . $this->id)
                    ->body('Transaksi telah ditambahkan.')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                DB::rollback();
                Notification::make()
                    ->title("Gagal menambahkan jimpitan " . $this->id)
                    ->body('Transaksi gagal ditambahkan.')
                    ->danger()
                    ->send();
                Log::error('Transaction processing failed: ' . $e->getMessage(), ['exception' => $e]);
                throw $e;
            }
            // ProcessTransaction::dispatch($this, TransactionType::Contribution, TransactionAction::Create, $this->withdrawls()->sum('value'), $user);
        }
    }

    // mark contribution as calculation complete
    public function cancelCalc(User $user)
    {
        if ($this->is_calculation_complete) {
            DB::beginTransaction();
            try {
                $transaction = Transaction::where('transactionable_type', get_class($this))
                    ->where('transactionable_id', $this->id)
                    ->with('balance')
                    ->first();

                if (!$transaction) {
                    throw new \Exception('Transaction not found.');
                }

                $originalValue = $transaction->balance->value;

                // Update the balance by removing the original value
                $this->updateBalance($transaction, $originalValue, true);

                $transaction->forceDelete();

                $this->update([
                    'is_calculation_complete' => false
                ]);

                DB::commit();
                Notification::make()
                    ->title("Berhasil membatalkan jimpitan " . $this->id)
                    ->body('Transaksi telah dibatalkan.')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                DB::rollback();
                Notification::make()
                    ->title("Gagal membatalkan jimpitan " . $this->id)
                    ->body('Transaksi gagal dibatalkan.')
                    ->danger()
                    ->send();
                Log::error('Transaction processing failed: ' . $e->getMessage(), ['exception' => $e]);
                throw $e;
            }
            // ProcessTransaction::dispatch($this, TransactionType::Contribution, TransactionAction::Delete, $this->withdrawls()->sum('value'), $user);
            // return $this->update([
            //     'is_calculation_complete' => false
            // ]);
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

    protected function updateBalance(Transaction $transaction, int $originalValue = null, bool $isDelete = false): void
    {
        $currentBalance = Balance::latest()->lockForUpdate()->first();

        if ($originalValue !== null) {
            // Adjust the balance by removing the original value first if it's an edit or delete
            switch ($transaction->type) {
                case TransactionType::Contribution:
                    $currentBalance->value -= $originalValue;
                    break;
                case TransactionType::Expense:
                    $currentBalance->value += $originalValue;
                    break;
            }
        }

        if (!$isDelete) {
            // Then update the balance with the new value if it's not a delete action
            switch ($transaction->type) {
                case TransactionType::Contribution:
                    $newBalanceValue = ($currentBalance->value ?? 0) + $this->withdrawls()->sum('value');
                    break;
                case TransactionType::Expense:
                    $newBalanceValue = ($currentBalance->value ?? 0) - $this->withdrawls()->sum('value');
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid transaction type!");
            }

            $balance = new Balance([
                'value' => $newBalanceValue,
                'date' => Carbon::now(),
                'transaction_id' => $transaction->id
            ]);
            $balance->save();
        } else {
            // If it's a delete action, just update the balance without creating a new record
            // Do permanent delete
            $transaction->balance->forceDelete();
            $currentBalance->save();
        }
    }
}
