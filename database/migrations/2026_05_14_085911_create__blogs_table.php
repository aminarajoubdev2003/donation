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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('title');
            $table->string('cover_image');
            $table->enum('category', [
            'أخبار المشاريع','حملات جديدة',
            'تقارير التوزيع','قصص نجاح',
            'تنبيهات عاجلة','فعاليات',
            'شركات و منظمات','غير ذلك'
            ]);
            $table->string('on_the_other_hand')->nullable();
            $table->string('excerpt');
            $table->text('content');
            $table->json('images');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_blogs');
    }
};
