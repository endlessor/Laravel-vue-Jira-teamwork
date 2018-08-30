<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function($table) {

            $table->dropColumn('email');
            $table->dropColumn('password');
            $table->dropColumn('remember_token');

            $table->string('username')->after('name');
            $table->string('key')->after('id');
            $table->integer('tenant_id')->unsigned()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function($table) {

            $table->string('email')->length(191)->unique();
            $table->string('password');
            $table->rememberToken();

            $table->dropForeign('users_tenant_id_foreign');

            $table->dropColumn('tenant_id');
            $table->dropColumn('username');
            $table->dropColumn('key');

        });
    }
}
