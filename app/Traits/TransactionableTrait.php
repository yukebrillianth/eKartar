<?php

namespace App\Traits;

use App\Enums\TransactionType;
use App\Models\Balance;
use App\Models\Transaction;

trait TransactionableTrait
{
    protected function updateBalance(Transaction $transaction): void
    {
        $currentBalance = Balance::OrderBy('id', 'desc')->latest()->lockForUpdate()->first();

        switch ($transaction->type) {
            case TransactionType::Debit:
                $newBalanceValue = ($currentBalance->value ?? 0) + $transaction->value;
                break;
            case TransactionType::Credit:
                $newBalanceValue = ($currentBalance->value ?? 0) - $transaction->value;
                break;
            default:
                throw new \InvalidArgumentException("Invalid transaction type!");
        }

        Balance::create([
            'value' => $newBalanceValue,
            'date' => now(),
            'transaction_id' => $transaction->id,
        ]);
    }
}
