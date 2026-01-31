<x-filament-widgets::widget>
    <div class="space-y-6">
        <!-- الرصيد النهائي -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">رصيد الخزينة</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($this->getFinalBalance(), 2) }} <span class="text-lg text-gray-500">LYD</span>
                    </p>
                </div>
                <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center">
                    <x-heroicon-o-wallet class="w-8 h-8 text-primary-600 dark:text-primary-400" />
                </div>
            </div>
        </div>

        <!-- الدخل والمصروفات -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- الدخل -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">الدخل</h3>
                    <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-success-500" />
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                    {{ number_format($this->getTotalIncome(), 2) }} <span class="text-sm text-gray-500">LYD</span>
                </p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>إيرادات المعاملات:</span>
                        <span class="font-medium">{{ number_format($this->getRevenue(), 2) }} LYD</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>الإيداع:</span>
                        <span class="font-medium">{{ number_format($this->getDeposits(), 2) }} LYD</span>
                    </div>
                </div>
            </div>

            <!-- المصروفات -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">المصروفات</h3>
                    <x-heroicon-o-arrow-trending-down class="w-5 h-5 text-danger-500" />
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                    {{ number_format($this->getExpenses(), 2) }} <span class="text-sm text-gray-500">LYD</span>
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">من سجل المصروفات</p>
            </div>
        </div>

        <!-- الربح من المعاملات -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">الربح من المعاملات</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">الإيرادات</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ number_format($this->getRevenue(), 2) }} <span class="text-xs text-gray-500">LYD</span>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">التكلفة</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ number_format($this->getCost(), 2) }} <span class="text-xs text-gray-500">LYD</span>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">صافي الربح</p>
                    <p class="text-lg font-semibold text-success-600 dark:text-success-400">
                        {{ number_format($this->getNetProfit(), 2) }} <span class="text-xs text-gray-500">LYD</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
