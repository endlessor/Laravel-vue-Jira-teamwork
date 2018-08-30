<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->increments('id');

            $table->string('key');
            $table->string('clientKey');
            $table->string('publicKey');
            $table->string('sharedSecret');
            $table->string('serverVersion');
            $table->string('pluginsVersion');
            $table->string('baseUrl');
            $table->string('productType');
            $table->string('description')->nullable();
            $table->string('eventType');

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
        Schema::dropIfExists('tenants');
    }
}
