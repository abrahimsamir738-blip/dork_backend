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
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->string('name');
            $table->string('address');
            $table->decimal('consultation_fee', 10, 2);
            $table->string('working_hours');
            $table->integer('current_serving_number')->default(0);
            $table->integer('max_patients_per_day')->default(30);
            $table->boolean('is_closed_today')->default(false);
            $table->string('photo')->nullable();
            $table->string('map_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
