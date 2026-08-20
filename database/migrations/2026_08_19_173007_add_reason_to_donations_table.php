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
        Schema::table('donations', function (Blueprint $table) {
            $table->enum('reason', ['عدم التطابق بين تاريخ الدفع وتاريخ الملف ', 'عدم التطابق بين المبلغ المدفوع والمبلغ الموجود داخل الملف'])
            ->default(NULL);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->string('on_the_other_hand')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            //
        });
    }
};
