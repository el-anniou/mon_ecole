<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Demandes ET rendez-vous dans une seule table (schéma validé) :
 * une demande publique naît `en_attente` / source `site` ; la propriétaire
 * peut aussi créer un rendez-vous manuellement (source `manuel`).
 *
 * - Souhait souple (CDC, décision n°4) : date + période (matin /
 *   après-midi / soir) + message libre — jamais d'heure imposée.
 * - `debut` (datetime) n'est fixé qu'à la confirmation ou en création
 *   manuelle ; les conflits de créneaux sont signalés applicativement.
 * - Aucune confirmation automatique n'est envoyée (CDC §8).
 *
 * NB : le lien facultatif vers une cliente (`cliente_id`) sera ajouté à
 * l'étape 7, lorsque la table `clientes` existera.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->id();
            $table->string('statut', 20)->default('en_attente')
                ->comment('en_attente, confirme, refuse, annule, termine');
            $table->string('source', 10)->default('site')->comment('site ou manuel');
            $table->string('nom_contact', 100);
            $table->string('moyen_contact', 150)->comment('Téléphone, WhatsApp ou e-mail laissé par la cliente');
            $table->date('date_souhaitee')->nullable();
            $table->string('periode_souhaitee', 15)->nullable()->comment('matin, apres_midi, soir');
            $table->text('message')->nullable();
            $table->foreignId('prestation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('realisation_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('debut')->nullable()->comment('Fixé à la confirmation ou en création manuelle');
            $table->unsignedInteger('duree_minutes')->nullable();
            $table->text('notes_privees')->nullable()->comment('Visibles uniquement en administration');
            $table->timestamps();

            $table->index(['statut', 'debut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rendez_vous');
    }
};
