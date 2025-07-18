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
         Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->unique();
            $table->string('title');
            $table->text('preview')->nullable();
            $table->string('cover_image')->nullable();
            $table->bigInteger('author_id')->unsigned()->nullable();
            $table->foreign('author_id')->references('id')->on('authors') ->onDelete('cascade'); ;
            $table->decimal('price', 10, 2);
            $table->boolean('is_physical')->default(false)->nullable();
            $table->string('sound_path')->nullable();
            $table->string('file_path')->nullable();
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->foreign('category_id')->references('id')->on('categories') ->onDelete('cascade'); ;
            $table->integer('copies')->default(0)->nullable();
            $table->string('publisher')->nullable();
            $table->string('language')->nullable();
            $table->float('rating')->default(0);
            $table->integer('raterscount')->default(0);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
