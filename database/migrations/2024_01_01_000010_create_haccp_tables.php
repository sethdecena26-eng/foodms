<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master checklist items (seeded once, reused every shift)
        Schema::create('haccp_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');                            // e.g., "Check fridge temps"
            $table->text('description')->nullable();
            $table->enum('category', [
                'personal_hygiene', 'temperature_control',
                'cross_contamination', 'cleaning', 'storage', 'other'
            ]);
            $table->enum('applies_to', ['opening', 'closing', 'both']);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Each completed checklist submission (per shift per day)
        Schema::create('haccp_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('shift_type', ['opening', 'closing']);
            $table->date('checklist_date');
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->text('supervisor_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['checklist_date', 'shift_type']); // one per shift per day
            $table->index(['checklist_date', 'shift_type']);
        });

        // Individual item responses within a checklist submission
        Schema::create('haccp_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('haccp_checklist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('haccp_item_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['pass', 'fail', 'na'])->default('na');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['haccp_checklist_id', 'haccp_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('haccp_checklist_items');
        Schema::dropIfExists('haccp_checklists');
        Schema::dropIfExists('haccp_items');
    }
};
