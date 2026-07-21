<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('communication_packages')) {
            Schema::create('communication_packages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('channel', 20);
                $table->unsignedInteger('unit_price')->default(0);
                $table->unsignedInteger('minimum_quantity')->default(1);
                $table->string('currency', 8)->default('XOF');
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['channel', 'is_active']);
            });
        }

        if (!Schema::hasTable('communication_credit_balances')) {
            Schema::create('communication_credit_balances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
                $table->string('channel', 20);
                $table->unsignedInteger('purchased')->default(0);
                $table->unsignedInteger('used')->default(0);
                $table->timestamps();

                $table->unique(['organization_id', 'channel'], 'communication_balances_org_channel_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_credit_balances');
        Schema::dropIfExists('communication_packages');
    }
};
