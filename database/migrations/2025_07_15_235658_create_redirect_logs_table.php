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
            $table->string('ip', 15)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->json('headers')->nullable();
            $table->json('query_params')->nullable();
            $table->timestamps();

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
