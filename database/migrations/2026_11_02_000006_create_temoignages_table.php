<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Témoignages de clientes (CDC §8).
 *
 * Verrou de confidentialité : `publie` ne peut être vrai que si
 * `autorisation_affichage` l'est — contrainte imposée par le modèle
 * Temoignage (événement saving), en plus des contrôles d'interface.
 *
 * NB : le lien facultatif vers une cliente (`cliente_id`) sera ajouté à
 * l'étape 7, lorsque la table `clientes` existera.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temoignages', function (Blueprint $table) {
            $table->id();
            $table->string('auteur', 100)->comment('Prénom ou pseudonyme, selon le consentement');
            $table->text('contenu');
            $table->boolean('autorisation_affichage')->default(false);
            $table->date('autorisation_notee_le')->nullable()->comment('Date à laquelle le consentement a été recueilli');
            $table->string('autorisation_note')->nullable()->comment('Canal / preuve du consentement (ex. « accord WhatsApp du 12/01 »)');
            $table->boolean('publie')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temoignages');
    }
};
