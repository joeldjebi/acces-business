<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('type_de_tarifications')) {
            Schema::create('type_de_tarifications', function (Blueprint $table) {
                $table->id();
                $table->string('libelle', 100);
                $table->tinyInteger('statut')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_de_tarifications');
    }
};
