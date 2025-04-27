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
        Schema::create('inventorys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('placement');
            $table->datetime('asset_entry')->nullable();
            $table->datetime('expired_date')->nullable();
            $table->boolean('is_can_loan');
            $table->enum('inv_status', ['owned', 'loan']);
            $table->integer('quantity');
            $table->string('img_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventorys');
    }
};