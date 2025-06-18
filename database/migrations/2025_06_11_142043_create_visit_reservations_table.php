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
        Schema::create('visit_reservations', function (Blueprint $table) {
             $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
    $table->string('guest_name')->nullable();
    $table->date('visit_date');
    $table->time('start_time');
    $table->time('end_time');
    $table->enum('status', ['pending', 'checked_in', 'cancelled','done'])->default('pending');
    $table->uuid('code')->unique(); // كود فريد للباركود
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_reservations');
    }
};
