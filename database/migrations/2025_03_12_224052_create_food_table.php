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
        Schema::create('food', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name', 45);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price');
            $table->string('price_afterdiscount', 20)->nullable();
            $table->string('percent', 4)->nullable();
            $table->boolean('is_ready')->default(true);
            $table->boolean('is_promo')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food');
    }
};
