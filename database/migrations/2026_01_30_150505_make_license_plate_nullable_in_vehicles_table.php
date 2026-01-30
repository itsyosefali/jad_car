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
            // Drop the unique constraint first
            $table->dropUnique(['رقم_اللوحة', 'الصنف']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            // Make license plate nullable
            $table->string('رقم_اللوحة')->nullable()->change();
        });

        Schema::table('vehicles', function (Blueprint $table) {
            // Re-add unique constraint (NULL values are allowed and each NULL is considered unique)
            $table->unique(['رقم_اللوحة', 'الصنف']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique(['رقم_اللوحة', 'الصنف']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            // Make license plate required again
            $table->string('رقم_اللوحة')->nullable(false)->change();
        });

        Schema::table('vehicles', function (Blueprint $table) {
            // Re-add unique constraint
            $table->unique(['رقم_اللوحة', 'الصنف']);
        });
    }
};
