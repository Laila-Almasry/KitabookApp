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
          Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('book_id')->unsigned();
            $table->foreign('book_id')->references('id')->on('books') ->onDelete('cascade'); ;
            $table->bigInteger('order_item_id')->unsigned()->nullable();
            $table->foreign('order_item_id')->references('id')->on('order_items') ->onDelete('cascade'); ;
            $table->string('barcode')->unique(); // unique per copy
            $table->enum('status', ['available', 'borrowed', 'damaged', 'lost', 'reserved','sold'])->default('available');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_copies');
    }
};
