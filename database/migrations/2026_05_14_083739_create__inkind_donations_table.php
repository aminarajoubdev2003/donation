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
        Schema::create('inkind_donations', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->foreignId('governorate_id')->references('id')->on('governorates')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger("user_id");
            $table->foreign("user_id")->references("id")->on("users")->onDelete("CASCADE")->onUpdate("CASCADE");
            $table->string('name_of_material');
            $table->integer('amount');
            $table->enum('type',['أثاث' ,'أدوات منزلية', 'أجهزة طبية', 'أجهزة إلكترونية', 'ملابس', 'أدوات مدرسية', 'غير ذلك']);
            $table->string('on_the_other_hand')->nullable();
            $table->json('images');
            $table->enum('status_of_materail',['جديدة','مستعملة']);
            $table->enum('status',['تم استلامه','لم يتم استلامه بعد'])->default('لم يتم استلامه بعد');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_inkind_donations');
    }
};
