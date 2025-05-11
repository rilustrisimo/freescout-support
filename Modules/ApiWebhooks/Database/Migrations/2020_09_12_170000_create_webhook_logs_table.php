<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebhookLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('webhook_id')->index();
            $table->integer('status_code');
            $table->text('error');
            $table->string('event', 255);
            $table->longText('data')->nullable();
            $table->unsignedTinyInteger('attempts')->default(1);
            $table->boolean('finished')->default(false)->index();
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
        Schema::dropIfExists('webhook_logs');
    }
}
