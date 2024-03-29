<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('text_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('businessId')->references('id')->on('businesses')->onDelete('cascade')->default(0);
            $table->text('header')->nullable();
            $table->text('body');
            $table->string('url')->nullable();
            $table->string('promoCode')->nullable();
            $table->string('sendToType')->default('*');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('text_messages');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
};
