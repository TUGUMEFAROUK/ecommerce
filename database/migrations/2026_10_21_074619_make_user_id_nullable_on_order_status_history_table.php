<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Webhook-driven status transitions have no human actor, so the
     * history's user must become optional. SQLite cannot drop foreign
     * keys in place; there Laravel rebuilds the table for the column
     * change and carries the existing key over, so the drop is only
     * issued on drivers that support it.
     */
    public function up(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->dropForeign(['user_id']);
            }

            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            }
        });
    }
};
