<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stock can be restored by the system rather than by a person — for
     * example when an order is cancelled — so the actor who made the
     * movement is optional. The same reasoning already applies to
     * `order_status_history.user_id`.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
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
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            }
        });
    }
};
