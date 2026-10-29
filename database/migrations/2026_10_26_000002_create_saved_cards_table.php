<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('saved_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcel_claim_id')->constrained()->cascadeOnDelete();
            $table->text('authorization_code');
            $table->char('authorization_hash', 64);
            $table->string('card_type')->nullable();
            $table->string('last4', 4)->nullable();
            $table->string('exp_month', 2)->nullable();
            $table->string('exp_year', 4)->nullable();
            $table->string('customer_email')->index();
            $table->string('paystack_customer_code')->nullable();
            $table->boolean('reusable')->default(false);
            $table->timestamps();
            $table->unique(['parcel_claim_id', 'authorization_hash']);
        });
    }

    public function down(): void { Schema::dropIfExists('saved_cards'); }
};
