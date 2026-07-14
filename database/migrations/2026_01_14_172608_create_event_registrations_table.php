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
        if (!Schema::hasTable('event_registrations')) {
            Schema::create('event_registrations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('event_id');
                $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
                $table->string('email');
                $table->string('nom')->nullable();
                $table->string('prenom')->nullable();
                $table->string('telephone')->nullable();
                $table->string('entreprise')->nullable();
                $table->enum('statut_reponse', ['en_attente', 'present', 'peut_etre', 'absent'])->default('en_attente');
                $table->string('token_unique')->unique();
                $table->string('qr_code_path')->nullable();
                $table->boolean('carte_envoyee')->default(false);
                $table->timestamp('date_inscription')->nullable();
                $table->timestamp('date_reponse')->nullable();
                $table->timestamp('date_validation_otp')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                $table->timestamps();
                
                $table->index(['event_id', 'email']);
                $table->index('token_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
