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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('رقم_اللوحة');
            $table->string('رقم_الهيكل')->nullable();
            $table->string('الصنف'); // سيارة / شاحنة / دراجة / آلية
            $table->string('اللون')->nullable();
            $table->integer('سنة_الصنع')->nullable();
            $table->text('ملاحظات')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['رقم_اللوحة', 'الصنف']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
