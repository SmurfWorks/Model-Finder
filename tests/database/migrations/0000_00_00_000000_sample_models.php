<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SampleModels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'users',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('email');
                $table->string('password')->nullable();
                $table->unsignedBigInteger('role_id');
                $table->boolean('receive_newsletter')->default(false);
                $table->timestamps();
                $table->softDeletes();
            }
        );

        Schema::create(
            'roles',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->timestamps();
                $table->softDeletes();
            }
        );

        Schema::create(
            'permissions',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->timestamps();
                $table->softDeletes();
            }
        );

        Schema::create(
            'roles__permissions',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('permission_id');
                $table->timestamps();
                $table->softDeletes();
            }
        );
    }
}
