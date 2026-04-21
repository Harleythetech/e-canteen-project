<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->string('status', 20)->default('pending');
            $table->time('pickup_time')->nullable();
            $table->text('special_instructions')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('paymongo_checkout_id')->nullable();
            $table->string('paymongo_payment_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index('paymongo_checkout_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
