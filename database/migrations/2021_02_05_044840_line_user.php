<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LineUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_users', function (Blueprint $table) {
            $table->id();
            $table->string('line_id')->index();
            $table->string('line_name')->nullable();
            $table->string('name_sei')->nullable();
            $table->string('name_mei')->nullable();
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
        Schema::dropIfExists('line_users');
    }
}
