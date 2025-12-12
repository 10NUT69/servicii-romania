<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {

            if (!Schema::hasColumn('services', 'images_tmp')) {
                $table->json('images_tmp')->nullable()->after('images');
            }

            if (!Schema::hasColumn('services', 'fail_reason')) {
                $table->text('fail_reason')->nullable()->after('published_at');
            }

            if (!Schema::hasColumn('services', 'queued_at')) {
                $table->timestamp('queued_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'images_tmp')) {
                $table->dropColumn('images_tmp');
            }
            if (Schema::hasColumn('services', 'fail_reason')) {
                $table->dropColumn('fail_reason');
            }
            if (Schema::hasColumn('services', 'queued_at')) {
                $table->dropColumn('queued_at');
            }
        });
    }
};
