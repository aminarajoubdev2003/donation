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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('name')->unique();
            $table->decimal('target_amount', 15, 2);
            $table->string('image');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('purposes');
            $table->enum('status', [
            'جديدة','نشطة','متوقفة','مكتملة', 'منتهية','ملغاة'])->default('جديدة');
            $table->decimal('collected_amount', 15, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
