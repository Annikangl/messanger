<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_files', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('message_id')->nullable();
            $table->foreign('message_id')->references('id')->on('messages')
                ->onDelete('cascade');
            $table->string('filename');
            $table->string('extension');
            $table->bigInteger('size')->unsigned();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('message_files');
    }
}
