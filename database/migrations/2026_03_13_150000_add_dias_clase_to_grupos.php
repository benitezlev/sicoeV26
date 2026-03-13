<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->jsonb('dias_clase')->default('[1,2,3,4,5]')->after('total_horas'); // 1=LU, 2=MA, 3=MI, 4=JU, 5=VI, 6=SA, 7=DO
        });
    }

    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->dropColumn('dias_clase');
        });
    }
};
