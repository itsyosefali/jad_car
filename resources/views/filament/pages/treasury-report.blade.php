<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filters -->
        <x-filament::section class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">🔍 تصفية البيانات</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">📅 من تاريخ</label>
                        <input type="date" wire:model.live="startDate" 
                               class="w-full rounded-lg border-2 border-blue-300 dark:border-blue-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 font-medium text-gray-900 dark:text-gray-100 px-4 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">📅 إلى تاريخ</label>
                        <input type="date" wire:model.live="endDate" 
                               class="w-full rounded-lg border-2 border-blue-300 dark:border-blue-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 font-medium text-gray-900 dark:text-gray-100 px-4 py-2">
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Summary -->
        @php
            $summary = $this->getSummary();
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-filament::section class="bg-gradient-to-br from-green-500 to-emerald-600 shadow-lg border-2 border-green-400">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-bold text-green-100 mb-2">💰 إجمالي الدخل</div>
                        <div class="text-3xl font-extrabold text-white">
                            {{ number_format($summary['total_income'], 2) }}
                        </div>
                        <div class="text-xs font-semibold text-green-100 mt-1">LYD</div>
                    </div>
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                        <x-heroicon-o-arrow-trending-up class="w-8 h-8 text-white" />
                    </div>
                </div>
            </x-filament::section>
            
            <x-filament::section class="bg-gradient-to-br from-red-500 to-rose-600 shadow-lg border-2 border-red-400">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-bold text-red-100 mb-2">💸 إجمالي المصروفات</div>
                        <div class="text-3xl font-extrabold text-white">
                            {{ number_format($summary['total_expenses'], 2) }}
                        </div>
                        <div class="text-xs font-semibold text-red-100 mt-1">LYD</div>
                    </div>
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                        <x-heroicon-o-arrow-trending-down class="w-8 h-8 text-white" />
                    </div>
                </div>
            </x-filament::section>
            
            <x-filament::section class="bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg border-2 border-blue-400">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-bold text-blue-100 mb-2">💼 الرصيد النهائي</div>
                        <div class="text-3xl font-extrabold text-white">
                            {{ number_format($summary['final_balance'], 2) }}
                        </div>
                        <div class="text-xs font-semibold text-blue-100 mt-1">LYD</div>
                    </div>
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                        <x-heroicon-o-wallet class="w-8 h-8 text-white" />
                    </div>
                </div>
            </x-filament::section>
            
            <x-filament::section class="bg-gradient-to-br from-purple-500 to-pink-600 shadow-lg border-2 border-purple-400">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-bold text-purple-100 mb-2">📊 عدد السجلات</div>
                        <div class="text-3xl font-extrabold text-white">
                            {{ number_format($summary['count'], 0) }}
                        </div>
                        <div class="text-xs font-semibold text-purple-100 mt-1">سجل</div>
                    </div>
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                        <x-heroicon-o-document-text class="w-8 h-8 text-white" />
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- Table -->
        <x-filament::section class="shadow-xl">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-document-text class="w-6 h-6 text-primary-600" />
                    <span class="text-xl font-bold text-gray-900 dark:text-white">📋 سجل الخزينة (GL Entry)</span>
                </div>
            </x-slot>
            
            <div class="overflow-x-auto rounded-lg border-2 border-gray-200 dark:border-gray-700">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gradient-to-r from-gray-800 to-gray-900 dark:from-gray-700 dark:to-gray-800">
                        <tr>
                            <th class="px-6 py-4 text-right text-sm font-bold text-white uppercase tracking-wider border-r border-gray-600">📅 التاريخ</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-white uppercase tracking-wider border-r border-gray-600">🏷️ النوع</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-white uppercase tracking-wider border-r border-gray-600">📝 الوصف</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-white uppercase tracking-wider border-r border-gray-600">👤 العميل</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-white uppercase tracking-wider border-r border-gray-600">🔖 المرجع</th>
                            <th class="px-6 py-4 text-left text-sm font-bold text-white uppercase tracking-wider border-r border-gray-600">💵 المبلغ</th>
                            <th class="px-6 py-4 text-left text-sm font-bold text-white uppercase tracking-wider">💰 الرصيد المتراكم</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($this->getEntries() as $entry)
                            <tr class="hover:bg-blue-50 dark:hover:bg-gray-800 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                        {{ $entry->التاريخ->format('Y-m-d') }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $entry->التاريخ->format('l') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $color = match($entry->النوع) {
                                            'إيراد' => 'success',
                                            'إيداع' => 'info',
                                            'مصروف' => 'danger',
                                            default => 'gray',
                                        };
                                        $icon = match($entry->النوع) {
                                            'إيراد' => '↑',
                                            'إيداع' => '💰',
                                            'مصروف' => '↓',
                                            default => '•',
                                        };
                                    @endphp
                                    <x-filament::badge :color="$color" size="lg" class="font-bold">
                                        {{ $icon }} {{ $entry->النوع }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $entry->الوصف }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $entry->العميل }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                        {{ $entry->المرجع }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-extrabold {{ $entry->النوع === 'مصروف' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                        {{ $entry->النوع === 'مصروف' ? '➖' : '➕' }} {{ number_format($entry->المبلغ, 2) }}
                                    </div>
                                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">LYD</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-extrabold text-blue-600 dark:text-blue-400">
                                        {{ number_format($entry->الرصيد_المتراكم, 2) }}
                                    </div>
                                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">LYD</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="text-4xl mb-4">📭</div>
                                    <div class="text-lg font-bold text-gray-500 dark:text-gray-400">
                                        لا توجد سجلات في هذا النطاق الزمني
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
