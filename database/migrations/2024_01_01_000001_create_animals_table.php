<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('espece');
            $table->string('race');
            $table->date('date_naissance')->nullable();
            $table->text('historique_sante')->nullable();
            $table->decimal('poids', 8, 2)->nullable();
            $table->enum('sexe', ['M', 'F'])->nullable();
            $table->enum('statut', ['actif', 'inactif', 'malade', 'mort'])->default('actif');
            $table->foreignId('employe_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('animals');
    }
}; 