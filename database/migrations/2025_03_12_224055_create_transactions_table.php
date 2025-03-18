<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use phpDocumentor\Reflection\Types\Nullable;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barcode_id')->nullable()->constrained('barcodes')->cascadeOnDelete();
            $table->string('code', 6)->nullable();
            $table->string('name', 45);
            $table->string('phone', 16)->nullable();
            $table->string('external_id', 150)->nullable();
            $table->string('checkout_link', 150)->nullable();
            $table->string('payment_method', 45)->nullable();
            $table->unsignedTinyInteger('status')->default(0);
            $table->boolean('is_cashpay')->default(true);
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('ppn')->nullable();
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
