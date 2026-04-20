<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // e.g., ORD-20240101-0001
            $table->foreignId('user_id')->constrained()->comment('Cashier/Employee');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'gcash', 'other'])->default('cash');

            // Financials
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 4)->default(0);   // sum of ingredient costs
            $table->decimal('net_profit', 10, 4)->default(0);   // total_amount - total_cost
            $table->decimal('amount_tendered', 10, 2)->nullable();
            $table->decimal('change_due', 10, 2)->nullable();

            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index('created_at'); // for daily/range queries
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
