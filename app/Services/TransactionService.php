<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * إكمال المعاملة
     */
    public function completeTransaction(Transaction $transaction): bool
    {
        if (!$transaction->isDraft()) {
            throw new \Exception('لا يمكن إكمال معاملة غير مسودة.');
        }

        return DB::transaction(function () use ($transaction) {
            $transaction->update([
                'الحالة' => 'مكتملة',
            ]);

            return true;
        });
    }

    /**
     * التحقق من إمكانية تعديل المعاملة
     */
    public function canModify(Transaction $transaction): bool
    {
        return $transaction->isDraft();
    }

    /**
     * التحقق من إمكانية حذف المعاملة
     */
    public function canDelete(Transaction $transaction): bool
    {
        return !$transaction->isCompleted();
    }

    /**
     * التحقق من إمكانية تعديل السعر
     */
    public function canModifyPrice(Transaction $transaction): bool
    {
        return !$transaction->isCompleted();
    }
}
