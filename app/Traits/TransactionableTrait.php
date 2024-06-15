<?php

namespace App\Traits;

use App\Enums\TransactionType;
use App\Models\Balance;
use App\Models\Transaction;
use Carbon\Carbon;

trait TransactionableTrait
{
    protected function updateBalance(Transaction $transaction, int $originalValue = null, int $value = null, bool $isDelete = false): void
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
                    $newBalanceValue = ($currentBalance->value ?? 0) + $value;
                    break;
                case TransactionType::Expense:
                    $newBalanceValue = ($currentBalance->value ?? 0) - $value;
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
