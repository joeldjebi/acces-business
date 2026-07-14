<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('nom', 120)->unique();
                $table->string('indicatif', 12)->nullable();
                $table->string('currency', 12)->nullable();
                $table->string('flag', 16)->nullable();
                $table->tinyInteger('statut')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
                $table->string('nom', 120);
                $table->tinyInteger('statut')->default(1);
                $table->timestamps();

                $table->unique(['country_id', 'nom']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('countries');
    }
};
