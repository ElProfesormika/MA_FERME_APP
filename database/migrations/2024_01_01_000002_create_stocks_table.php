<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('produit');
            $table->integer('quantite');
            $table->string('unite');
            $table->date('date_entree');
            $table->date('date_peremption')->nullable();
            $table->decimal('prix_unitaire', 10, 2)->nullable();
            $table->string('fournisseur')->nullable();
            $table->string('categorie')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stocks');
    }
}; 