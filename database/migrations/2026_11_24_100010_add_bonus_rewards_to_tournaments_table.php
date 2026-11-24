<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->decimal('bonus_top1', 3, 1)->default(4.0)->after('advancing_count');
            $table->decimal('bonus_top2', 3, 1)->default(3.0)->after('bonus_top1');
            $table->decimal('bonus_top3', 3, 1)->default(2.0)->after('bonus_top2');
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn(['bonus_top1', 'bonus_top2', 'bonus_top3']);
        });
    }
};
