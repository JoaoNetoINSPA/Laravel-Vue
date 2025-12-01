<?php

use App\Models\Loan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->timestamp('due_at');
        });

        // Important: Backfill due_at for existing Loan rows inside the migration class.
        foreach(Loan::all() as $loan) {
            $due_at = Carbon::now()->plus(days: 14);

            if ($loan->loaned_at) {
                $due_at = $loan->loaned_at->plus(days: 14);
            }

            $loan->due_at = $due_at;
            $loan->update();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('due_at');
        });
    }
};
