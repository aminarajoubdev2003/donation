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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('name');
            $table->unsignedBigInteger("district_id");
            $table->foreign("district_id")->references("id")->on("districts")->onDelete("CASCADE")->onUpdate("CASCADE");
            $table->decimal('estimated_cost', 15, 2);
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->text('requirements');
            $table->string('cover_image')->nullable();
            $table->enum('sector', ['تعليمي', 'صحي', 'إغاثي','إعمار','خدمي','غير ذلك']);
            $table->string('on_the_other_hand')->nullable();
            $table->json('images')->nullable();
            $table->json('videos')->nullable();
            $table->enum('funding_source',['رجال أعمال', 'منظمات', 'تبرعات']);
            $table->string('Implementing_party');
            $table->enum('status',['متوقف','قيد التنفيذ','مكتمل','مخطط له']);
            $table->unique(['district_id','name']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
