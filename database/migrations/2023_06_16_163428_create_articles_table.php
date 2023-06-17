<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;

class CreateArticlesTable extends Migration
{
    private $tableName = 'articles';

    public function up()
    {
        $schemaBuilder = app(Builder::class);

        $schemaBuilder->create($this->tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('identifier')->unique();
            $table->string('title', 200);
            $table->string('body', 1000);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('user_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        $schemaBuilder = app(Builder::class);

        $schemaBuilder->dropIfExists($this->tableName);
    }
}
