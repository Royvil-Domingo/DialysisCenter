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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_code')->unique()->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birthdate');
            $table->enum('sex', ['male', 'female']);
            $table->string('contact_number')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_number')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('dialysis_access_type')->nullable();
            $table->decimal('dry_weight_kg', 5, 2)->nullable();
            $table->text('allergies')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
