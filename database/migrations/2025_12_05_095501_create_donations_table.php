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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->unsignedBigInteger("user_id");
            $table->foreign("user_id")->references("id")->on("users")->onDelete("CASCADE")->onUpdate("CASCADE");
            $table->unsignedBigInteger("campaign_id");
            $table->foreign("campaign_id")->references("id")->on("campaigns")->onDelete("CASCADE")->onUpdate("CASCADE");
            $table->decimal('contribution_amount', 15, 2);
            $table->enum('currency_type', ['SYP', 'USD', 'EUR']);
            $table->text('contribution_details')->nullable();
            $table->boolean('pledge_to_donate')->default(0);
            $table->boolean('donate_directly')->default(0);
            $table->decimal('usd_amount', 15, 2)->default(0);
            $table->string('image')->default('null');
            $table->enum('status', ['متوافق', 'غير متوافق', 'قيد التدقيق']);
            $table->boolean('pending')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
