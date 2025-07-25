<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('redirect_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('redirect_id');
            $table->string('ip', 15)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('header_referer', 2048)->nullable();
            $table->json('query_params')->nullable();
            $table->timestamps();

            $table->foreign('redirect_id')->references('id')->on('redirects')->onDelete('cascade');

            $table->index(['id', 'ip', 'created_at']); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redirect_logs');
    }
};
