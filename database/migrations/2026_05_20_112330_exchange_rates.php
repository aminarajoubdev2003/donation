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
        Schema::create('exchange_rates', function (Blueprint $table) {
        $table->id();
        $table->uuid();
        $table->enum('currency', [
            'SYP',
            'EUR'
        ])->unique();

        // كم تساوي 1 USD من هذه العملة
        $table->decimal('rate', 15, 2)->default(0);

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
