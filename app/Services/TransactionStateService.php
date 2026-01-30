<?php

namespace App\Services;

use App\Models\Transaction;

class TransactionStateService
{
    /**
     * التحقق من صحة تغيير الحالة
     */
    public function canChangeStatus(Transaction $transaction, string $newStatus): bool
    {
        $currentStatus = $transaction->الحالة;

        // حالات صالحة
        $validTransitions = [
            'مسودة' => ['مكتملة', 'ملغاة'],
            'مكتملة' => [], // لا يمكن تغيير حالة المعاملة المكتملة
            'ملغاة' => [], // لا يمكن تغيير حالة المعاملة الملغاة
        ];

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }

    /**
     * تغيير حالة المعاملة
     */
    public function changeStatus(Transaction $transaction, string $newStatus): bool
    {
        if (! $this->canChangeStatus($transaction, $newStatus)) {
            throw new \Exception("لا يمكن تغيير الحالة من '{$transaction->الحالة}' إلى '{$newStatus}'.");
        }

        $transaction->update([
            'الحالة' => $newStatus,
        ]);

        return true;
    }

    /**
     * الحصول على الحالات المتاحة للمعاملة الحالية
     */
    public function getAvailableStatuses(Transaction $transaction): array
    {
        $currentStatus = $transaction->الحالة;

        $availableStatuses = [
            'مسودة' => ['مكتملة', 'ملغاة'],
            'مكتملة' => [],
            'ملغاة' => [],
        ];

        return $availableStatuses[$currentStatus] ?? [];
    }
}
