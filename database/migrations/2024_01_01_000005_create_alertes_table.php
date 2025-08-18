<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alertes', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->text('message');
            $table->boolean('critique')->default(false);
            $table->enum('statut', ['nouvelle', 'en_cours', 'résolue'])->default('nouvelle');
            $table->foreignId('animal_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('stock_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('employe_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamp('date_resolution')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('alertes');
    }
}; 