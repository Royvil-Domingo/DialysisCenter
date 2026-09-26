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
        Schema::create('treatment_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->date('session_date');
            $table->decimal('pre_weight_kg', 5, 2)->nullable();
            $table->decimal('post_weight_kg', 5, 2)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('blood_pressure_pre')->nullable();
            $table->string('blood_pressure_post')->nullable();
            $table->string('dialyzer_used')->nullable();
            $table->text('complications')->nullable();
            $table->enum('status', ['in_progress', 'completed'])->default('completed');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatment_records');
    }
};
