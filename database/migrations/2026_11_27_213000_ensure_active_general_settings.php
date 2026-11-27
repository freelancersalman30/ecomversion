<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure that at least one general setting is active
        $activeCount = DB::table('general_settings')->where('status', 1)->count();
        if ($activeCount === 0) {
            DB::table('general_settings')->limit(1)->update(['status' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
