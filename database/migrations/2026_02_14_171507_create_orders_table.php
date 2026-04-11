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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->string('slot_id')->nullable();
            $table->string('patient_name');
            $table->string('phone_number');
            $table->integer('queue_number');
            $table->enum('type', ['كشف', 'استشارة', 'تحاليل', 'أشعة'])->default('كشف');
            $table->enum('status', ['منتظر', 'قيد الكشف', 'تم الكشف', 'ملغي'])->default('منتظر');
            $table->enum('payment_status', ['تم دفع الرسوم', 'لم يتم الدفع'])->default('لم يتم الدفع');
            $table->date('date');
            $table->text('notes')->nullable();
            $table->decimal('consultation_fee', 10, 2);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
