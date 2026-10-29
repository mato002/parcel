<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('transactions', 'paystack_reference')) {
            Schema::table('transactions', fn (Blueprint $table) => $table->renameColumn('paystack_reference', 'gateway_reference'));
        }
        if (! Schema::hasColumn('transactions', 'gateway')) {
            Schema::table('transactions', fn (Blueprint $table) => $table->string('gateway', 40)->default('paystack')->after('saved_card_id'));
        }

        $cardIndexes = collect(Schema::getIndexes('saved_cards'))->pluck('name');
        if (! $cardIndexes->contains('saved_cards_claim_fk_idx')) {
            Schema::table('saved_cards', fn (Blueprint $table) => $table->index('parcel_claim_id', 'saved_cards_claim_fk_idx'));
        }
        if ($cardIndexes->contains('saved_cards_parcel_claim_id_authorization_hash_unique')) {
            Schema::table('saved_cards', fn (Blueprint $table) => $table->dropUnique('saved_cards_parcel_claim_id_authorization_hash_unique'));
        }
        if (Schema::hasColumn('saved_cards', 'paystack_customer_code')) {
            Schema::table('saved_cards', fn (Blueprint $table) => $table->renameColumn('paystack_customer_code', 'gateway_customer_code'));
        }
        if (! Schema::hasColumn('saved_cards', 'gateway')) {
            Schema::table('saved_cards', fn (Blueprint $table) => $table->string('gateway', 40)->default('paystack')->after('parcel_claim_id'));
        }
        $cardIndexes = collect(Schema::getIndexes('saved_cards'))->pluck('name');
        if (! $cardIndexes->contains('saved_cards_parcel_claim_id_gateway_authorization_hash_unique')) {
            Schema::table('saved_cards', fn (Blueprint $table) => $table->unique(['parcel_claim_id', 'gateway', 'authorization_hash']));
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'gateway')) Schema::table('transactions', fn (Blueprint $table) => $table->dropColumn('gateway'));
        if (Schema::hasColumn('transactions', 'gateway_reference')) Schema::table('transactions', fn (Blueprint $table) => $table->renameColumn('gateway_reference', 'paystack_reference'));

        $cardIndexes = collect(Schema::getIndexes('saved_cards'))->pluck('name');
        if (! $cardIndexes->contains('saved_cards_parcel_claim_id_authorization_hash_unique')) Schema::table('saved_cards', fn (Blueprint $table) => $table->unique(['parcel_claim_id', 'authorization_hash']));
        if ($cardIndexes->contains('saved_cards_parcel_claim_id_gateway_authorization_hash_unique')) Schema::table('saved_cards', fn (Blueprint $table) => $table->dropUnique(['parcel_claim_id', 'gateway', 'authorization_hash']));
        if (Schema::hasColumn('saved_cards', 'gateway')) Schema::table('saved_cards', fn (Blueprint $table) => $table->dropColumn('gateway'));
        if (Schema::hasColumn('saved_cards', 'gateway_customer_code')) Schema::table('saved_cards', fn (Blueprint $table) => $table->renameColumn('gateway_customer_code', 'paystack_customer_code'));
        $cardIndexes = collect(Schema::getIndexes('saved_cards'))->pluck('name');
        if ($cardIndexes->contains('saved_cards_claim_fk_idx')) Schema::table('saved_cards', fn (Blueprint $table) => $table->dropIndex('saved_cards_claim_fk_idx'));
    }
};
