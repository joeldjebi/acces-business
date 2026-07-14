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
        if (Schema::hasTable('events')) {
            return;
        }
        
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            
            // Relations
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Dates et horaires
            $table->date('date_debut');
            $table->time('heure_debut');
            $table->date('date_fin');
            $table->time('heure_fin');
            $table->string('fuseau_horaire')->default('Europe/Paris')->nullable();
            
            // Localisation
            $table->string('lieu')->nullable();
            $table->text('adresse_complete')->nullable();
            $table->string('ville')->nullable();
            $table->string('code_postal')->nullable();
            $table->string('pays')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Organisation
            $table->string('organisateur')->nullable();
            $table->string('email_contact')->nullable();
            $table->string('telephone')->nullable();
            $table->string('site_web')->nullable();
            
            // Statut et visibilité
            $table->enum('statut', ['brouillon', 'publie', 'annule', 'termine', 'reporte'])->default('brouillon');
            $table->unsignedBigInteger('visibilite_id');
            $table->foreign('visibilite_id')->references('id')->on('visibilites')->onDelete('restrict');
            $table->timestamp('date_publication')->nullable();
            
            // Capacité et inscriptions
            $table->integer('capacite_maximale')->nullable();
            $table->boolean('inscription_requise')->default(false);
            $table->date('date_limite_inscription')->nullable();
            
            // Tarification
            $table->unsignedBigInteger('type_tarification_id');
            $table->foreign('type_tarification_id')->references('id')->on('type_de_tarifications')->onDelete('restrict');
            $table->decimal('prix', 10, 2)->nullable();
            $table->unsignedBigInteger('devise_id')->nullable();
            $table->foreign('devise_id')->references('id')->on('devises')->onDelete('restrict');
            
            // Métadonnées
            $table->string('tags')->nullable();
            $table->integer('vues')->default(0);
            $table->text('notes_internes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index('statut');
            $table->index('date_debut');
            $table->index('category_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
