<?php

namespace App\Traits;

use App\Enums\TransactionType;
use App\Models\Balance;
use App\Models\Transaction;
use Carbon\Carbon;

trait TransactionableTrait
{
    protected function updateBalance(Transaction $transaction, int $value = null): void
    {
        $currentBalance = Balance::latest()->lockForUpdate()->first();

        switch ($transaction->type) {
            case TransactionType::Debit:
                $newBalanceValue = ($currentBalance->value ?? 0) + $value;
                break;
            case TransactionType::Credit:
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
    }
}
