<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This pivot table IS the "Recipe".
     * Each row says: "MenuItem X requires Y units of Ingredient Z."
     */
    public function up(): void
    {
        Schema::create('ingredient_menu_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('ingredient_id')
                  ->constrained()
                  ->cascadeOnDelete();
            // How many units of this ingredient are needed per 1 serving of the menu item
            $table->decimal('quantity_required', 10, 3);
            $table->timestamps();

            $table->unique(['menu_item_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_menu_item');
    }
};
