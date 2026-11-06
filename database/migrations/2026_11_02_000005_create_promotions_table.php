<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Promotions (CDC §7) : visibles publiquement uniquement lorsqu'elles sont
 * publiées ET dans leur période de validité ; masquées automatiquement à
 * la date de fin. L'administration conserve l'historique.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('texte');
            $table->date('debut')->nullable();
            $table->date('fin')->nullable();
            $table->string('lien_action')->nullable();
            $table->boolean('publiee')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
