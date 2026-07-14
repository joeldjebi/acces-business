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
        if (!Schema::hasTable('event_access_links')) {
            Schema::create('event_access_links', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('event_id');
                $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
                $table->string('email_destinataire');
                $table->string('token_unique')->unique();
                $table->unsignedBigInteger('envoye_par');
                $table->foreign('envoye_par')->references('id')->on('users')->onDelete('cascade');
                $table->timestamp('envoye_le')->nullable();
                $table->timestamp('utilise_le')->nullable();
                $table->boolean('est_utilise')->default(false);
                $table->timestamps();
                
                $table->index(['event_id', 'email_destinataire']);
                $table->index('token_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_access_links');
    }
};
