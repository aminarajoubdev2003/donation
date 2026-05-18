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
        Schema::create('details', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->foreignId('project_id')->references('id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
            $table->string('detail',255);
            $table->decimal('cost', 15, 2);
            $table->unique(['project_id','detail']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('details');
    }
};
