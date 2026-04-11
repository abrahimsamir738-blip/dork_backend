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
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->integer('day_of_week'); // 0 = Saturday, 1 = Sunday, etc.
            $table->string('day_name'); // 'السبت', 'الأحد', etc.
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration'); // in minutes
            $table->enum('type', ['كشف', 'استشارة'])->default('كشف');
            $table->integer('capacity')->default(10);
            $table->integer('booked_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
