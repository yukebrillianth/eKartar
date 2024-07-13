<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Expense;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class TransactionService.
 */
class TransactionService
{
    public static function addBankCharge(int $value, string $date)
    {
        $roles = ['admin', 'super_admin'];
        $recipient = User::whereHas('roles', function ($query) use ($roles) {
            $query->whereIn('name', $roles);
        })->get();

        DB::beginTransaction();
        try {
            $expense = new Expense();
            $expense->title = "Biaya admin bank " . Carbon::parse(now('Asia/Jakarta'))->locale("id_ID")->toDateString();
            $expense->description = "Biaya admin bank bulanan";
            $expense->value = $value;
            $expense->date = $date;
            $expense->user_id = User::where('email', 'system@ekartar.my.id')->first()->id;
            $expense->save();

            DB::commit();
            Log::info('Success create bank charge [Date]: ' . $date . " [Value]: " . $value);
            Notification::make()
                ->title('Biaya admin bank ' . Carbon::parse(now('Asia/Jakarta'))->locale("id_ID")->toDateString() . ' berhasil!')
                ->body('Berhasil menambahkan biaya admin bank senilai Rp. ' . $value)
                ->success()
                ->sendToDatabase($recipient);
        } catch (\Throwable $e) {
            DB::rollback();
            Notification::make()
                ->title('Biaya admin bank ' . Carbon::parse(now('Asia/Jakarta'))->locale("id_ID")->toDateString() . ' gagal!')
                ->body('Gagal menambahkan biaya admin bank senilai Rp. ' . $value)
                ->danger()
                ->sendToDatabase($recipient);
            Log::error('Cant create bank charge [Date]: ' . $date . " [Value]: " . $value . " [Message]: " . $e->getMessage(), ['exception' => $e]);
            \Sentry\captureMessage('Transaction processing failed: ' . $e->getMessage());
            \Sentry\captureException($e);
        }
    }
}
