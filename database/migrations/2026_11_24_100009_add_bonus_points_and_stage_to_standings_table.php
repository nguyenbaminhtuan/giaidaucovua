<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('standings', function (Blueprint $table) {
            $table->string('stage')->default('group_stage')->after('group_id');
            $table->decimal('bonus_points', 3, 1)->default(0.0)->after('points');
        });
    }

    public function down(): void
    {
        Schema::table('standings', function (Blueprint $table) {
            $table->dropColumn(['stage', 'bonus_points']);
        });
    }
};
