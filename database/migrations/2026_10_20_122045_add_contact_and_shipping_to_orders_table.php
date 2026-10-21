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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('shipping_line1')->nullable();
            $table->string('shipping_line2')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state', 100)->nullable();
            $table->string('shipping_postal_code', 20)->nullable();
            $table->string('shipping_country', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'contact_name',
                'contact_email',
                'contact_phone',
                'shipping_line1',
                'shipping_line2',
                'shipping_city',
                'shipping_state',
                'shipping_postal_code',
                'shipping_country',
            ]);
        });
    }
};
