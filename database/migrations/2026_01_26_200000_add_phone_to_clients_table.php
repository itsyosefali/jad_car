<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, migrate existing phone data from client_phones to clients
        // Get the first phone for each client
        $clientsWithPhones = DB::table('client_phones')
            ->select('client_id', DB::raw('MIN(رقم_الهاتف) as رقم_الهاتف'))
            ->groupBy('client_id')
            ->get();

        // Add phone column to clients table
        Schema::table('clients', function (Blueprint $table) {
            $table->string('رقم_الهاتف')->nullable()->after('الرقم_الوطني');
        });

        // Migrate data
        foreach ($clientsWithPhones as $row) {
            DB::table('clients')
                ->where('id', $row->client_id)
                ->update(['رقم_الهاتف' => $row->رقم_الهاتف]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('رقم_الهاتف');
        });
    }
};
