<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            $table->string('stage')->default('group_stage')->after('group_id'); // 'group_stage' or 'knockout'
            $table->string('stage_name')->nullable()->after('stage'); // 'Tứ Kết', 'Bán Kết', 'Chung Kết', 'Tranh Hạng 3'
        });
    }

    public function down(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            $table->dropColumn(['stage', 'stage_name']);
        });
    }
};
