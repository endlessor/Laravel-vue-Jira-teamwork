<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJiraTeamworkLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jira_teamwork_links', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('jira_project_id')->unsigned();
            $table->integer('teamwork_project_id')->unsigned();

            $table->foreign('jira_project_id')->references('id')->on('jira_projects');
            $table->foreign('teamwork_project_id')->references('id')->on('teamwork_projects');

            $table->string('teamwork_default_list')->default('Backlog');

            $table->timestamps();
        });

        Schema::dropIfExists('jira_teamwork_projects');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jira_teamwork_links');

        Schema::create('jira_teamwork_projects', function(Blueprint $table) {

            $table->increments('id');

            $table->integer('jira_project_id')->unsigned();
            $table->integer('teamwork_project_id')->unsigned();

            $table->foreign('jira_project_id')->references('id')->on('jira_projects');
            $table->foreign('teamwork_project_id')->references('id')->on('teamwork_projects');

            $table->timestamps();

        });
    }
}
