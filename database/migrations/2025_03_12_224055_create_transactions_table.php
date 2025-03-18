<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barcode_id')->constrained('barcodes')->cascadeOnDelete();
            $table->string('code', 6);
            $table->string('name', 45);
            $table->string('phone', 16);
            $table->string('external_id', 150);
            $table->string('checkout_link', 150);
            $table->string('payment_method', 45)->nullable();
            $table->unsignedTinyInteger('status')->default(0);
            $table->boolean('is_cashpay')->default(true);
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('ppn');
            $table->unsignedBigInteger('total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
