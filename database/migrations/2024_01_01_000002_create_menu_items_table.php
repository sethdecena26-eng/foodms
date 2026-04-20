<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category'); // e.g., Burgers, Drinks, Sides
            $table->decimal('selling_price', 10, 2);
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            // Computed/cached columns (updated by observer)
            $table->decimal('computed_cost', 10, 4)->default(0);  // sum of ingredient costs
            $table->decimal('computed_profit_margin', 5, 2)->default(0); // %
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
