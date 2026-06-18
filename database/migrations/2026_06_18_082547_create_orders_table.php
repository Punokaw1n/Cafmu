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
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('table_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['new', 'processing', 'ready', 'completed'])->default('new');
            $table->enum('payment_status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->string('payment_url')->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
