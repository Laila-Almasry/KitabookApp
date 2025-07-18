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
        Schema::create('purchases', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('book_copy_id');
    $table->decimal('price', 8, 2);
    $table->timestamp('purchased_at');
    $table->timestamps();

    $table->foreign('book_copy_id')->references('id')->on('book_copies')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
