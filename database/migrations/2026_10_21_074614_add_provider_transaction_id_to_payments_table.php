<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `provider_reference` already stores the merchant-side tx_ref (unique).
     * This column stores the payment provider's own transaction id, which
     * is what webhooks and verify calls are keyed by.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('provider_transaction_id')->nullable()->after('provider_reference');

            $table->index('provider_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['provider_transaction_id']);
            $table->dropColumn('provider_transaction_id');
        });
    }
};
