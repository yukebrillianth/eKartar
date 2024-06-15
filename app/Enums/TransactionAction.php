<?php

namespace App\Enums;

enum TransactionAction: string
{
    case Create = 'create';
    case Edit = 'edit';
    case Delete = 'delete';
}
