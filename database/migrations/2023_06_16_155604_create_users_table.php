<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    private $tableName = 'users';

    public function up()
    {
        $schemaBuilder = app(Builder::class);

        $schemaBuilder->create($this->tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('identifier')->unique();
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        $schemaBuilder = app(Builder::class);

        $schemaBuilder->dropIfExists($this->tableName);
    }
}
