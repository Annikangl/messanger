<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageAudioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_audio', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('message_id')->nullable();
            $table->foreign('message_id')->references('id')->on('messages')
                ->onDelete('cascade');
            $table->string('audio');

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
        Schema::dropIfExists('message_audio');
    }
}
