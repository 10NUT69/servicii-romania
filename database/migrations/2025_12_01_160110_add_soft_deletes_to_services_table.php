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
            // Cand intra in coada (pending)
            $table->timestamp('queued_at')->nullable()->after('status');

            // Lista fisierelor urcate "raw" (temporar), inainte de procesare
            $table->json('images_tmp')->nullable()->after('images');

            // Motivul erorii daca publicarea esueaza
            // published_at exista deja la tine, deci punem fail_reason dupa el
            $table->text('fail_reason')->nullable()->after('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['queued_at', 'images_tmp', 'fail_reason']);
        });
    }
};
