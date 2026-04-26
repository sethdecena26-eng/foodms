<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temperature_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();

            $table->string('location');                          // e.g., "Walk-in Fridge", "Freezer 1"
            $table->enum('location_type', ['fridge', 'freezer', 'hot_hold', 'other']);
            $table->decimal('temperature_celsius', 5, 2);        // recorded temp
            $table->decimal('min_safe_celsius', 5, 2);           // safe range minimum
            $table->decimal('max_safe_celsius', 5, 2);           // safe range maximum
            // is_within_range is a computed PHP attribute on the model, not a DB column

            $table->enum('shift', ['opening', 'midday', 'closing']);
            $table->date('log_date');
            $table->time('log_time');
            $table->text('corrective_action')->nullable();        // filled if out of range
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['log_date', 'location']);
            $table->index(['log_date', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temperature_logs');
    }
};