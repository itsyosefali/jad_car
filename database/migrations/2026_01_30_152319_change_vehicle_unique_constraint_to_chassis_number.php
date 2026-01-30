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
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop the old unique constraint
            $table->dropUnique(['رقم_اللوحة', 'الصنف']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            // Make chassis number required and unique
            $table->string('رقم_الهيكل')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop the unique constraint on chassis number
            $table->dropUnique(['رقم_الهيكل']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            // Make chassis number nullable again
            $table->string('رقم_الهيكل')->nullable()->change();
        });

        Schema::table('vehicles', function (Blueprint $table) {
            // Restore the old unique constraint
            $table->unique(['رقم_اللوحة', 'الصنف']);
        });
    }
};
