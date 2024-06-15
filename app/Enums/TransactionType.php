<?php

namespace App\Enums;

enum TransactionType: string
{
    case Expense = 'expense';
    case Contribution = 'contribution';
    case Cash = 'cash';
}
