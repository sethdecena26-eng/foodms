<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Immutable audit trail for every stock change.
     * Inserted via DB::table() — never updated, never deleted.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->comment('Who performed the action');

            $table->enum('type', ['stock_in', 'stock_out', 'adjustment', 'sale_deduction']);

            $table->decimal('quantity_before', 10, 3);
            $table->decimal('quantity_changed', 10, 3); // positive = in, negative = out
            $table->decimal('quantity_after', 10, 3);

            // Polymorphic reference to the source (Order, ManualAdjustment, etc.)
            $table->nullableMorphs('source');  // source_type, source_id

            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent(); // no updated_at — immutable

            $table->index(['ingredient_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
