<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('source');
            $table->string('external_reference');
            $table->string('guest_name');
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedInteger('rooms')->default(1);
            $table->string('status')->default('confirmed');
            $table->timestamps();

            $table->unique(['source', 'external_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};