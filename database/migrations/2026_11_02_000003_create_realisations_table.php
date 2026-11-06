<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réalisations de la galerie (CDC §6).
 *
 * - `reference` : identifiant stable pour les liens et le message WhatsApp ;
 * - `titre` facultatif (une réalisation peut être publiée sans titre) ;
 * - `image_dossier` : répertoire du stockage public contenant l'original
 *   et les variantes optimisées (vignette, grande) ;
 * - `publiee` : brouillon/publiée ;
 * - `position` : ordre d'affichage ajustable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('titre')->nullable();
            $table->text('description')->nullable();
            $table->string('texte_alternatif')->nullable();
            $table->string('image_dossier');
            $table->boolean('publiee')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->date('realisee_le')->nullable();
            $table->timestamps();
        });

        Schema::create('categorie_realisation', function (Blueprint $table) {
            $table->foreignId('categorie_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('realisation_id')->constrained('realisations')->cascadeOnDelete();
            $table->primary(['categorie_id', 'realisation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorie_realisation');
        Schema::dropIfExists('realisations');
    }
};
