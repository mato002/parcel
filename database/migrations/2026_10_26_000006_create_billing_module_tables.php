<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('billing_products')) Schema::create('billing_products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3);
            $table->string('interval_unit', 12)->default('month');
            $table->unsignedSmallInteger('interval_count')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index(['active', 'currency']);
        });

        Schema::create('billing_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcel_claim_id')->constrained()->cascadeOnDelete();
            $table->foreignId('saved_card_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('billing_product_id')->nullable()->constrained('billing_products')->nullOnDelete();
            $table->string('product_name', 120);
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3);
            $table->string('interval_unit', 12);
            $table->unsignedSmallInteger('interval_count')->default(1);
            $table->string('status', 20)->default('active');
            $table->dateTime('starts_at');
            $table->timestamp('next_billing_at')->nullable();
            $table->timestamp('last_billed_at')->nullable();
            $table->timestamp('retry_at')->nullable();
            $table->unsignedTinyInteger('failed_attempts')->default(0);
            $table->dateTime('consented_at');
            $table->text('consent_record');
            $table->foreignId('consented_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'next_billing_at']);
            $table->index(['status', 'retry_at']);
        });

        Schema::create('billing_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('scheduled_for');
            $table->string('dedupe_key', 120)->unique();
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->string('status', 20)->default('pending');
            $table->text('failure_reason')->nullable();
            $table->timestamp('retry_at')->nullable();
            $table->timestamps();
            $table->index(['billing_subscription_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_attempts');
        Schema::dropIfExists('billing_subscriptions');
        Schema::dropIfExists('billing_products');
    }
};
