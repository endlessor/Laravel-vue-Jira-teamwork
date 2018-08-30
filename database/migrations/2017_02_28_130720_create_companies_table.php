<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teamwork_companies', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('teamwork_id');
            $table->string('name');

            $table->timestamps();

            $table->unique('teamwork_id');
        });

        Schema::create('teamwork_company_app', function(Blueprint $table)
        {
            $table->increments('id');

            $table->integer('company_id')->unsigned();
            $table->integer('app_id')->unsigned();

            $table->foreign('company_id')->references('id')->on('teamwork_companies');
            $table->foreign('app_id')->references('id')->on('teamwork_apps');

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
        Schema::dropIfExists('teamwork_company_app');
        Schema::dropIfExists('teamwork_companies');
    }
}
