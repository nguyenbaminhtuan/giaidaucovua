<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->string('playoff_title')
                ->nullable()
                ->after('advancing_count');

            $table->text('playoff_description')
                ->nullable()
                ->after('playoff_title');
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn(['playoff_title', 'playoff_description']);
        });
    }
};
