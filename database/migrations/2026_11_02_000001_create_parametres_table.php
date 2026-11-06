<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Paramètres administrables du site (CDC §9 « Messages et paramètres »).
 *
 * Stockage clé/valeur : nom de la marque, coordonnées, numéro WhatsApp,
 * textes d'accueil, réseaux sociaux… La liste des clés éditables est
 * définie dans config/parametres.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametres', function (Blueprint $table) {
            $table->id();
            $table->string('cle')->unique();
            $table->text('valeur')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres');
    }
};
