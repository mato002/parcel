<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcel_claim_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('saved_card_id')->nullable()->constrained()->nullOnDelete();
            $table->string('paystack_reference')->unique();
            $table->string('type')->default('charge');
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3);
            $table->string('status')->default('pending');
            $table->string('description')->nullable();
            $table->string('refund_reference')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['parcel_claim_id', 'status']);
        });
    }

    public function down(): void { Schema::dropIfExists('transactions'); }
};
