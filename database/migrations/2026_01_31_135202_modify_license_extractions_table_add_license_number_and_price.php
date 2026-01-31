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
        Schema::table('license_extractions', function (Blueprint $table) {
            $table->dropColumn('رقم_الوثيقة');
            $table->string('رقم_الرخصة')->after('id');
            $table->decimal('السعر', 10, 2)->after('التاريخ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('license_extractions', function (Blueprint $table) {
            $table->dropColumn(['رقم_الرخصة', 'السعر']);
            $table->string('رقم_الوثيقة')->after('id');
        });
    }
};
