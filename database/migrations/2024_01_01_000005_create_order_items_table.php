<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained()->restrictOnDelete();

            $table->integer('quantity');

            // Snapshot prices at time of sale (prices can change later)
            $table->decimal('unit_price', 10, 2);       // selling price at time of sale
            $table->decimal('unit_cost', 10, 4);        // ingredient cost at time of sale
            $table->decimal('line_total', 10, 2);       // unit_price * quantity
            $table->decimal('line_cost', 10, 4);        // unit_cost * quantity
            $table->decimal('line_profit', 10, 4);      // line_total - line_cost

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
