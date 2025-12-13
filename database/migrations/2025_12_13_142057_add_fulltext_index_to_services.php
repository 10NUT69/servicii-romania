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
        Schema::table('services', function (Blueprint $table) {
            // Creăm un index de tip FULLTEXT pe coloanele 'title' și 'description'.
            // Acesta permite căutarea rapidă și calculul relevanței.
            // Al doilea parametru ('search_index') este numele pe care îl dăm acestui index.
            $table->fullText(['title', 'description'], 'search_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Dacă rulăm comanda migrate:rollback, ștergem acest index
            // pentru a aduce baza de date la starea inițială.
            $table->dropIndex('search_index');
        });
    }
};