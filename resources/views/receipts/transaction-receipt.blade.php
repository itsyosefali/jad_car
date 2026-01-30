<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال - {{ $transaction->الرقم_المرجعي }}</title>
    <style>
        @page {
            margin: 10mm;
            size: 80mm auto;
        }
        body {
            font-family: 'DejaVu Sans', 'Arial Unicode MS', 'Tahoma', Arial, sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 10px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            unicode-bidi: embed;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #C40000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 5px;
            background-color: #C40000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
        }
        .header h1 {
            color: #C40000;
            margin: 5px 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header h2 {
            margin: 2px 0;
            font-size: 12px;
            color: #333;
        }
        .info-section {
            margin: 12px 0;
        }
        .info-row {
            display: table;
            width: 100%;
            margin: 4px 0;
        }
        .info-label {
            display: table-cell;
            width: 35%;
            font-weight: bold;
            color: #333;
            font-size: 9px;
        }
        .info-value {
            display: table-cell;
            width: 65%;
            color: #666;
            font-size: 9px;
        }
        .section-title {
            background-color: #f5f5f5;
            padding: 5px 8px;
            margin: 10px 0 6px 0;
            font-weight: bold;
            font-size: 10px;
            border-right: 3px solid #C40000;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            background-color: #C40000;
            color: white;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .highlight {
            font-weight: bold;
            color: #C40000;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .items-table th,
        .items-table td {
            padding: 4px 6px;
            text-align: right;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
        }
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #333;
        }
        .items-table td {
            color: #666;
        }
        .items-total {
            font-weight: bold;
            color: #C40000;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">ج</div>
        <h1>{{ isset($Arabic) ? $Arabic->utf8Glyphs('مكتب جاد') : 'مكتب جاد' }}</h1>
        <h2>{{ isset($Arabic) ? $Arabic->utf8Glyphs('لتسجيل جميع أنواع السيارات') : 'لتسجيل جميع أنواع السيارات' }}</h2>
    </div>

    <div class="info-section">
        <div class="section-title">{{ isset($Arabic) ? $Arabic->utf8Glyphs('معلومات المعاملة') : 'معلومات المعاملة' }}</div>
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('الرقم المرجعي:') : 'الرقم المرجعي:' }}</div>
            <div class="info-value"><span class="badge highlight">{{ isset($Arabic) ? $Arabic->utf8Glyphs($transaction->الرقم_المرجعي) : $transaction->الرقم_المرجعي }}</span></div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('نوع المعاملة:') : 'نوع المعاملة:' }}</div>
            <div class="info-value">{{ isset($Arabic) ? $Arabic->utf8Glyphs($transaction->نوع_المعاملة) : $transaction->نوع_المعاملة }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('الحالة:') : 'الحالة:' }}</div>
            <div class="info-value">{{ isset($Arabic) ? $Arabic->utf8Glyphs($transaction->الحالة) : $transaction->الحالة }}</div>
        </div>
        @if($transaction->items && $transaction->items->count() > 0)
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('السعر الإجمالي:') : 'السعر الإجمالي:' }}</div>
            <div class="info-value items-total">{{ number_format($transaction->السعر, 2) }} {{ isset($Arabic) ? $Arabic->utf8Glyphs('دينار ليبي') : 'دينار ليبي' }}</div>
        </div>
        @else
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('السعر:') : 'السعر:' }}</div>
            <div class="info-value">{{ number_format($transaction->السعر, 2) }} {{ isset($Arabic) ? $Arabic->utf8Glyphs('دينار ليبي') : 'دينار ليبي' }}</div>
        </div>
        @endif
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('تاريخ المعاملة:') : 'تاريخ المعاملة:' }}</div>
            <div class="info-value">{{ $transaction->تاريخ_المعاملة->format('Y-m-d') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('تاريخ الإدخال:') : 'تاريخ الإدخال:' }}</div>
            <div class="info-value">{{ $transaction->تاريخ_الإدخال->format('Y-m-d H:i') }}</div>
        </div>
    </div>

    <div class="info-section">
        <div class="section-title">{{ isset($Arabic) ? $Arabic->utf8Glyphs('معلومات العميل') : 'معلومات العميل' }}</div>
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('الاسم الكامل:') : 'الاسم الكامل:' }}</div>
            <div class="info-value">{{ isset($Arabic) ? $Arabic->utf8Glyphs($transaction->client->الاسم_الكامل) : $transaction->client->الاسم_الكامل }}</div>
        </div>
        @if($transaction->client->الرقم_الوطني)
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('الرقم الوطني:') : 'الرقم الوطني:' }}</div>
            <div class="info-value">{{ isset($Arabic) ? $Arabic->utf8Glyphs($transaction->client->الرقم_الوطني) : $transaction->client->الرقم_الوطني }}</div>
        </div>
        @endif
        @if($transaction->client->رقم_الهاتف)
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('رقم الهاتف:') : 'رقم الهاتف:' }}</div>
            <div class="info-value">{{ isset($Arabic) ? $Arabic->utf8Glyphs($transaction->client->رقم_الهاتف) : $transaction->client->رقم_الهاتف }}</div>
        </div>
        @endif
    </div>

    @if($transaction->items && $transaction->items->count() > 0)
    <div class="info-section">
        <div class="section-title">{{ isset($Arabic) ? $Arabic->utf8Glyphs('تفاصيل المعاملة') : 'تفاصيل المعاملة' }}</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>{{ isset($Arabic) ? $Arabic->utf8Glyphs('اسم الخدمة') : 'اسم الخدمة' }}</th>
                    <th>{{ isset($Arabic) ? $Arabic->utf8Glyphs('التكلفة') : 'التكلفة' }}</th>
                    <th>{{ isset($Arabic) ? $Arabic->utf8Glyphs('الكمية') : 'الكمية' }}</th>
                    <th>{{ isset($Arabic) ? $Arabic->utf8Glyphs('الإجمالي') : 'الإجمالي' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->items as $item)
                <tr>
                    <td>{{ isset($Arabic) ? $Arabic->utf8Glyphs($item->service_name) : $item->service_name }}</td>
                    <td>{{ number_format($item->selling_price, 2) }} LYD</td>
                    <td>{{ $item->الكمية }}</td>
                    <td>{{ number_format($item->total, 2) }} LYD</td>
                </tr>
                @if($item->الملاحظات)
                <tr>
                    <td colspan="4" style="font-size: 8px; color: #999; padding-right: 20px;">
                        {{ isset($Arabic) ? $Arabic->utf8Glyphs('ملاحظات: ' . $item->الملاحظات) : 'ملاحظات: ' . $item->الملاحظات }}
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr class="items-total">
                    <td colspan="3" style="text-align: left; padding-right: 10px;">{{ isset($Arabic) ? $Arabic->utf8Glyphs('المجموع الكلي:') : 'المجموع الكلي:' }}</td>
                    <td>{{ number_format($transaction->السعر, 2) }} LYD</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    @if($transaction->vehicle)
    <div class="info-section">
        <div class="section-title">{{ isset($Arabic) ? $Arabic->utf8Glyphs('معلومات المركبة') : 'معلومات المركبة' }}</div>
        @if($transaction->vehicle->رقم_اللوحة)
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('رقم اللوحة:') : 'رقم اللوحة:' }}</div>
            <div class="info-value">{{ isset($Arabic) ? $Arabic->utf8Glyphs($transaction->vehicle->رقم_اللوحة) : $transaction->vehicle->رقم_اللوحة }}</div>
        </div>
        @endif
        @if($transaction->vehicle->نوع_المركبة)
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('نوع المركبة:') : 'نوع المركبة:' }}</div>
            <div class="info-value">{{ isset($Arabic) ? $Arabic->utf8Glyphs($transaction->vehicle->نوع_المركبة) : $transaction->vehicle->نوع_المركبة }}</div>
        </div>
        @endif
        @if($transaction->vehicle->الصنف)
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('الصنف:') : 'الصنف:' }}</div>
            <div class="info-value">{{ isset($Arabic) ? $Arabic->utf8Glyphs($transaction->vehicle->الصنف) : $transaction->vehicle->الصنف }}</div>
        </div>
        @endif
        @if($transaction->vehicle->رقم_الهيكل)
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('رقم الهيكل:') : 'رقم الهيكل:' }}</div>
            <div class="info-value">{{ isset($Arabic) ? $Arabic->utf8Glyphs($transaction->vehicle->رقم_الهيكل) : $transaction->vehicle->رقم_الهيكل }}</div>
        </div>
        @endif
    </div>
    @endif

    @if($transaction->payments && $transaction->payments->count() > 0)
    <div class="info-section">
        <div class="section-title">{{ isset($Arabic) ? $Arabic->utf8Glyphs('معلومات الدفع') : 'معلومات الدفع' }}</div>
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('إجمالي المبلغ المدفوع:') : 'إجمالي المبلغ المدفوع:' }}</div>
            <div class="info-value">{{ number_format($transaction->total_paid, 2) }} {{ isset($Arabic) ? $Arabic->utf8Glyphs('دينار ليبي') : 'دينار ليبي' }}</div>
        </div>
        @if($transaction->remaining_amount > 0)
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('المبلغ المتبقي:') : 'المبلغ المتبقي:' }}</div>
            <div class="info-value">{{ number_format($transaction->remaining_amount, 2) }} {{ isset($Arabic) ? $Arabic->utf8Glyphs('دينار ليبي') : 'دينار ليبي' }}</div>
        </div>
        @endif
        @if($transaction->payments->count() > 1)
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('عدد الدفعات:') : 'عدد الدفعات:' }}</div>
            <div class="info-value">{{ $transaction->payments->count() }} {{ isset($Arabic) ? $Arabic->utf8Glyphs('دفعة') : 'دفعة' }}</div>
        </div>
        @endif
        @foreach($transaction->payments as $payment)
        <div class="info-row" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e5e7eb;">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('دفعة #') : 'دفعة #' }}{{ $loop->iteration }}:</div>
            <div class="info-value">{{ number_format($payment->المبلغ, 2) }} LYD - {{ isset($Arabic) ? $Arabic->utf8Glyphs($payment->طريقة_الدفع) : $payment->طريقة_الدفع }} ({{ $payment->تاريخ_الدفع->format('Y-m-d') }})</div>
        </div>
        @endforeach
    </div>
    @endif

    @if($transaction->inspection)
    <div class="info-section">
        <div class="section-title">{{ isset($Arabic) ? $Arabic->utf8Glyphs('معلومات الفحص') : 'معلومات الفحص' }}</div>
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('نوع الإجراء:') : 'نوع الإجراء:' }}</div>
            <div class="info-value">{{ isset($Arabic) ? $Arabic->utf8Glyphs($transaction->inspection->نوع_الإجراء) : $transaction->inspection->نوع_الإجراء }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('النتيجة:') : 'النتيجة:' }}</div>
            <div class="info-value">{{ isset($Arabic) ? $Arabic->utf8Glyphs($transaction->inspection->النتيجة) : $transaction->inspection->النتيجة }}</div>
        </div>
        @if($transaction->inspection->رقم_الوثيقة)
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('رقم الوثيقة:') : 'رقم الوثيقة:' }}</div>
            <div class="info-value">{{ isset($Arabic) ? $Arabic->utf8Glyphs($transaction->inspection->رقم_الوثيقة) : $transaction->inspection->رقم_الوثيقة }}</div>
        </div>
        @endif
        @if($transaction->inspection->ملاحظات)
        <div class="info-row">
            <div class="info-label">{{ isset($Arabic) ? $Arabic->utf8Glyphs('ملاحظات:') : 'ملاحظات:' }}</div>
            <div class="info-value">{{ isset($Arabic) ? $Arabic->utf8Glyphs($transaction->inspection->ملاحظات) : $transaction->inspection->ملاحظات }}</div>
        </div>
        @endif
    </div>
    @endif

    @if($transaction->الملاحظات)
    <div class="info-section">
        <div class="section-title">{{ isset($Arabic) ? $Arabic->utf8Glyphs('ملاحظات إضافية') : 'ملاحظات إضافية' }}</div>
        <div class="info-value">{{ isset($Arabic) ? $Arabic->utf8Glyphs($transaction->الملاحظات) : $transaction->الملاحظات }}</div>
    </div>
    @endif

    <div class="footer">
        <p>{{ isset($Arabic) ? $Arabic->utf8Glyphs('تم إنشاء هذا الإيصال تلقائياً من نظام إدارة معاملات تأمين المركبات') : 'تم إنشاء هذا الإيصال تلقائياً من نظام إدارة معاملات تأمين المركبات' }}</p>
        <p>{{ isset($Arabic) ? $Arabic->utf8Glyphs('مكتب جاد - لتسجيل جميع أنواع السيارات') : 'مكتب جاد - لتسجيل جميع أنواع السيارات' }}</p>
    </div>
</body>
</html>
