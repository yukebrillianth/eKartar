<?php

namespace App\Jobs;

use App\Enums\TransactionAction;
use App\Enums\TransactionType;
use App\Models\Balance;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessTransaction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public $data, public TransactionType $type, public TransactionAction $action, public int $value, public User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        switch ($this->action) {
            case TransactionAction::Create:
                $this->createTransaction();
                break;

            case TransactionAction::Edit:
                $this->editTransaction();
                break;

            case TransactionAction::Delete:
                $this->deleteTransaction();
                break;

            default:
                Log::error('Invalid transaction action.');
        }
    }

    protected function createTransaction(): void
    {
        echo "Creating new transaction for " . $this->data->id . "\n";
        DB::beginTransaction();
        try {
            $transaction = new Transaction();
            $transaction->type = $this->type;

            switch ($this->type) {
                case TransactionType::Contribution:
                    $transaction->title = "Jimpitan " . $this->data->date;
                    $transactionable = $this->data;
                    break;
                case TransactionType::Expense:
                    $transaction->title = "Pengeluaran " . $this->data->date;
                    $transactionable = $this->data;
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid transaction type!");
            }

            $transaction->transactionable()->associate($transactionable);
            $transaction->save();

            $this->updateBalance($transaction);

            DB::commit();
            echo "Transaction Successfully! \n";
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Transaction processing failed: ' . $e->getMessage(), ['exception' => $e]);
            $this->fail($e);
            throw $e;
        }

        echo "Finished at: " . date("Y-m-d H:i:s") . "\n";
    }

    protected function editTransaction(): void
    {
        echo "Editing transaction for " . $this->data->id . "\n";
        DB::beginTransaction();
        try {
            // Find the transaction by transactionable type and ID
            $transaction = Transaction::where('transactionable_type', get_class($this->data))
                ->where('transactionable_id', $this->data->id)
                ->with('balance')
                ->first();

            if (!$transaction) {
                throw new \Exception('Transaction not found.');
            }

            $originalValue = $transaction->balance->value;

            switch ($this->type) {
                case TransactionType::Contribution:
                    $transaction->title = "Jimpitan " . $this->data->date;
                    $transactionable = $this->data;
                    break;
                case TransactionType::Expense:
                    $transaction->title = "Pengeluaran " . $this->data->date;
                    $transactionable = $this->data;
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid transaction type!");
            }

            $transaction->transactionable()->associate($transactionable);
            $transaction->save();

            // Update the balance by adjusting the original value
            $this->updateBalance($transaction, $originalValue);

            DB::commit();
            echo "Transaction Successfully Edited! \n";
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Transaction editing failed: ' . $e->getMessage(), ['exception' => $e]);
            $this->fail($e);
            throw $e;
        }

        echo "Finished at: " . date("Y-m-d H:i:s") . "\n";
    }

    protected function deleteTransaction(): void
    {
        echo "Deleting transaction for " . $this->data->id . "\n";
        DB::beginTransaction();
        try {
            // Find the transaction by transactionable type and ID
            $transaction = Transaction::where('transactionable_type', get_class($this->data))
                ->where('transactionable_id', $this->data->id)
                ->with('balance')
                ->first();

            if (!$transaction) {
                throw new \Exception('Transaction not found.');
            }

            $originalValue = $transaction->balance->value;

            // Update the balance by removing the original value
            $this->updateBalance($transaction, $originalValue, true);

            $transaction->forceDelete();

            DB::commit();
            Notification::make()
                ->title("Berhasil " . $this->action->value . " jimpitan " . $this->data->id)
                ->success()
                ->sendToDatabase($this->user);
            echo "Transaction Successfully Deleted! \n";
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Transaction deletion failed: ' . $e->getMessage(), ['exception' => $e]);
            $this->fail($e);
            throw $e;
        }

        echo "Finished at: " . date("Y-m-d H:i:s") . "\n";
    }

    protected function updateBalance(Transaction $transaction, int $originalValue = null, bool $isDelete = false): void
    {
        $currentBalance = Balance::latest()->first();

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
                    $newBalanceValue = ($currentBalance->value ?? 0) + $this->value;
                    break;
                case TransactionType::Expense:
                    $newBalanceValue = ($currentBalance->value ?? 0) - $this->value;
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

    public function failed(?Throwable $exception): void
    {
        switch ($this->type) {
            case TransactionType::Contribution:
                Notification::make()
                    ->title("Gagal saat " . $this->action->value . " jimpitan")
                    ->danger()
                    ->body($exception->getMessage())
                    ->sendToDatabase($this->user);
                break;
            case TransactionType::Expense:
                Notification::make()
                    ->title("Gagal saat " . $this->action->value . " pengeluaran")
                    ->danger()
                    ->body($exception->getMessage())
                    ->sendToDatabase($this->user);
                break;
            default:
                throw new \InvalidArgumentException("Invalid transaction type!");
        }
    }
}
