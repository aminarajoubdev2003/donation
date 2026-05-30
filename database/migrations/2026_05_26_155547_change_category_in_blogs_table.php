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
        Schema::table('blogs', function (Blueprint $table) {
            $table->enum('category', [
            'أخبار المشاريع',
            'حملات جديدة',
            'تقارير التوزيع',
            'قصص نجاح',
            'تنبيهات عاجلة',
            'فعاليات',
            'شركات و منظمات',
            'غير ذلك'
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            //
        });
    }
};
