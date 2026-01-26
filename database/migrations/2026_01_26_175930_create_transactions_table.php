<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('الرقم_المرجعي')->unique();
            $table->string('نوع_المعاملة'); // تأمين / تجديد / فحص / استخراج رخصة
            $table->date('تاريخ_المعاملة');
            $table->timestamp('تاريخ_الإدخال')->useCurrent();
            $table->foreignId('client_id')->constrained('clients')->onDelete('restrict');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->decimal('السعر', 10, 2);
            $table->string('الحالة')->default('مسودة'); // مسودة / مكتملة / ملغاة
            $table->text('الملاحظات')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
