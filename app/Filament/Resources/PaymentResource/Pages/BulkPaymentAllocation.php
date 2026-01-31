<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class BulkPaymentAllocation extends Page
{
    protected static string $resource = PaymentResource::class;

    protected static string $view = 'filament.resources.payment-resource.pages.bulk-payment-allocation';

    protected static ?string $title = 'تخصيص دفعات للعملاء';

    protected static ?string $navigationLabel = 'تخصيص دفعات';

    public ?int $clientId = null;
    public ?string $paymentMethod = 'نقدي';
    public ?string $receiptNumber = null;
    public ?string $paymentDate = null;
    public array $selectedTransactions = [];
    public array $transactionAmounts = [];

    public function mount(): void
    {
        $this->paymentDate = now()->format('Y-m-d');
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('اختيار العميل')
                ->schema([
                    Select::make('clientId')
                        ->label('العميل')
                        ->options(Client::query()->pluck('الاسم_الكامل', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            $this->clientId = $state;
                            $this->selectedTransactions = [];
                            $this->transactionAmounts = [];
                        }),
                    Placeholder::make('client_info')
                        ->label('معلومات العميل')
                        ->content(function (Forms\Get $get) {
                            $clientId = $get('clientId');
                            if (!$clientId) {
                                return '—';
                            }
                            $client = Client::find($clientId);
                            if (!$client) {
                                return '—';
                            }
                            $unpaid = $client->getUnpaidTransactions();
                            $totalOwed = $unpaid->sum('السعر');
                            $totalPaid = $unpaid->sum(fn($t) => $t->total_paid);
                            $totalRemaining = $totalOwed - $totalPaid;
                            
                            return "إجمالي المستحقات: ".number_format($totalOwed, 2)." LYD\n".
                                   "المدفوع: ".number_format($totalPaid, 2)." LYD\n".
                                   "المتبقي: ".number_format($totalRemaining, 2)." LYD\n".
                                   "عدد المعاملات غير المدفوعة: ".$unpaid->count();
                        })
                        ->columnSpanFull(),
                ]),
            Section::make('المعاملات غير المدفوعة')
                ->schema([
                    CheckboxList::make('selectedTransactions')
                        ->label('اختر المعاملات للدفع')
                        ->options(function (Forms\Get $get) {
                            $clientId = $get('clientId');
                            if (!$clientId) {
                                return [];
                            }
                            $client = Client::find($clientId);
                            if (!$client) {
                                return [];
                            }
                            $unpaid = $client->getUnpaidTransactions();
                            
                            return $unpaid->mapWithKeys(function ($transaction) {
                                $remaining = $transaction->remaining_amount;
                                return [
                                    $transaction->id => $transaction->الرقم_المرجعي.' - '.number_format($transaction->السعر, 2).' LYD (متبقي: '.number_format($remaining, 2).' LYD)'
                                ];
                            })->toArray();
                        })
                        ->columns(1)
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            $this->selectedTransactions = $state ?? [];
                            // Initialize amounts for selected transactions
                            $this->transactionAmounts = [];
                            foreach ($this->selectedTransactions as $transactionId) {
                                $transaction = Transaction::find($transactionId);
                                if ($transaction) {
                                    $this->transactionAmounts[$transactionId] = [
                                        'transaction_id' => $transactionId,
                                        'amount' => $transaction->remaining_amount,
                                    ];
                                }
                            }
                        })
                        ->columnSpanFull(),
                    Repeater::make('transactionAmounts')
                        ->label('مبالغ الدفع')
                        ->schema([
                            Placeholder::make('transaction_ref')
                                ->label('المعاملة')
                                ->content(function ($record) {
                                    $transactionId = $record['transaction_id'] ?? null;
                                    $transaction = Transaction::find($transactionId);
                                    return $transaction ? $transaction->الرقم_المرجعي : '—';
                                }),
                            Placeholder::make('transaction_info')
                                ->label('معلومات المعاملة')
                                ->content(function ($record) {
                                    $transactionId = $record['transaction_id'] ?? null;
                                    $transaction = Transaction::find($transactionId);
                                    if (!$transaction) {
                                        return '—';
                                    }
                                    return "الإجمالي: ".number_format($transaction->السعر, 2)." LYD\n".
                                           "المدفوع: ".number_format($transaction->total_paid, 2)." LYD\n".
                                           "المتبقي: ".number_format($transaction->remaining_amount, 2)." LYD";
                                })
                                ->columnSpanFull(),
                            TextInput::make('amount')
                                ->label('مبلغ الدفعة')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->suffix('LYD')
                                ->default(function ($record) {
                                    $transactionId = $record['transaction_id'] ?? null;
                                    $transaction = Transaction::find($transactionId);
                                    return $transaction ? $transaction->remaining_amount : 0;
                                })
                                ->maxValue(function ($record) {
                                    $transactionId = $record['transaction_id'] ?? null;
                                    $transaction = Transaction::find($transactionId);
                                    return $transaction ? $transaction->remaining_amount : 0;
                                })
                                ->live(),
                            Forms\Components\Hidden::make('transaction_id')
                                ->required(),
                        ])
                        ->defaultItems(0)
                        ->itemLabel(function (array $state): ?string {
                            $transactionId = $state['transaction_id'] ?? null;
                            $transaction = Transaction::find($transactionId);
                            if (!$transaction) {
                                return null;
                            }
                            return $transaction->الرقم_المرجعي.' - '.number_format($state['amount'] ?? 0, 2).' LYD';
                        })
                        ->visible(fn (Forms\Get $get) => !empty($get('selectedTransactions')))
                        ->columnSpanFull(),
                ]),
            Section::make('معلومات الدفع')
                ->schema([
                    Select::make('paymentMethod')
                        ->label('طريقة الدفع')
                        ->options([
                            'نقدي' => 'نقدي',
                            'تحويل بنكي' => 'تحويل بنكي',
                            'شيك' => 'شيك',
                            'بطاقة' => 'بطاقة',
                            'أخرى' => 'أخرى',
                        ])
                        ->default('نقدي')
                        ->required(),
                    TextInput::make('receiptNumber')
                        ->label('رقم الإيصال')
                        ->maxLength(255),
                    DatePicker::make('paymentDate')
                        ->label('تاريخ الدفع')
                        ->required()
                        ->default(now())
                        ->displayFormat('Y-m-d'),
                    Placeholder::make('total_amount')
                        ->label('إجمالي المبلغ')
                        ->content(function (Forms\Get $get) {
                            $amounts = $get('transactionAmounts') ?? [];
                            $total = 0;
                            foreach ($amounts as $item) {
                                if (isset($item['amount'])) {
                                    $total += (float) $item['amount'];
                                }
                            }
                            return number_format($total, 2).' LYD';
                        })
                        ->columnSpanFull(),
                ]),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        
        if (empty($data['selectedTransactions']) || empty($data['transactionAmounts'])) {
            Notification::make()
                ->title('خطأ')
                ->body('يرجى اختيار معاملة واحدة على الأقل وإدخال المبالغ')
                ->danger()
                ->send();
            return;
        }

        $paymentMethod = $data['paymentMethod'] ?? 'نقدي';
        $receiptNumber = $data['receiptNumber'] ?? null;
        $paymentDate = $data['paymentDate'] ?? now();

        $createdCount = 0;
        $totalAmount = 0;
        $errors = [];

        foreach ($data['transactionAmounts'] as $item) {
            $transactionId = $item['transaction_id'] ?? null;
            $amount = (float) ($item['amount'] ?? 0);

            if (!$transactionId || $amount <= 0) {
                continue;
            }

            $transaction = Transaction::find($transactionId);
            if (!$transaction) {
                continue;
            }

            // Check if amount exceeds remaining
            if ($amount > $transaction->remaining_amount) {
                $errors[] = "المبلغ المدخل للمعاملة {$transaction->الرقم_المرجعي} يتجاوز المبلغ المتبقي (".number_format($transaction->remaining_amount, 2)." LYD)";
                continue;
            }

            // Create payment
            Payment::create([
                'transaction_id' => $transactionId,
                'المبلغ' => $amount,
                'طريقة_الدفع' => $paymentMethod,
                'رقم_الإيصال' => $receiptNumber,
                'تاريخ_الدفع' => $paymentDate,
            ]);

            $createdCount++;
            $totalAmount += $amount;
        }

        if (!empty($errors)) {
            Notification::make()
                ->title('تحذير')
                ->body(implode("\n", $errors))
                ->warning()
                ->send();
        }

        if ($createdCount > 0) {
            Notification::make()
                ->title('نجح')
                ->body("تم إنشاء {$createdCount} دفعة بإجمالي ".number_format($totalAmount, 2)." LYD")
                ->success()
                ->send();

            // Reset form
            $this->clientId = null;
            $this->selectedTransactions = [];
            $this->transactionAmounts = [];
            $this->form->fill();
        } else {
            Notification::make()
                ->title('خطأ')
                ->body('لم يتم إنشاء أي دفعات')
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('submit')
                ->label('حفظ الدفعات')
                ->submit('submit')
                ->color('success')
                ->icon('heroicon-o-check'),
        ];
    }
}
