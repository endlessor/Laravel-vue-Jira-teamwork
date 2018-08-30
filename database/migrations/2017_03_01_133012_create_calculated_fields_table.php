<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCalculatedFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calculated_fields', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('link_id')->unsigned();
            $table->foreign('link_id')->references('id')->on('jira_teamwork_links');

            $table->string('target_field');
            $table->text('formula');

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
        Schema::dropIfExists('calculated_fields');
    }
}
