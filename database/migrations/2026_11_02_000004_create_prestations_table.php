<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prestations (CDC §7) : le tarif peut représenter des pratiques
 * différentes sans imposer un prix fictif — cinq types de prix.
 * La devise est un paramètre global administrable (`parametres.devise`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestations', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('type_prix')->default('sur_demande'); // fixe, a_partir_de, fourchette, sur_demande, non_affiche
            $table->decimal('prix_min', 10, 2)->nullable();      // sert aussi de prix fixe
            $table->decimal('prix_max', 10, 2)->nullable();      // borne haute des fourchettes
            $table->unsignedInteger('duree_minutes')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('publiee')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestations');
    }
};
