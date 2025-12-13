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
        // 1. Adăugăm index pe tabela 'categories' pentru coloana 'name'
        Schema::table('categories', function (Blueprint $table) {
            // Indexul ajută căutarea rapidă când folosim orWhereHas pe categorii
            $table->index('name'); 
        });

        // 2. Adăugăm index pe tabela 'counties' pentru coloana 'name'
        Schema::table('counties', function (Blueprint $table) {
            // Indexul ajută căutarea rapidă când cineva scrie numele județului în search
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ștergem indexurile dacă dăm rollback
        Schema::table('categories', function (Blueprint $table) {
            // Drop index folosind array: Laravel știe că numele e categories_name_index
            $table->dropIndex(['name']);
        });

        Schema::table('counties', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};