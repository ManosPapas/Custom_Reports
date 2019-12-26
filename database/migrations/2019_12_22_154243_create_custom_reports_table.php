<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomReportsTable extends Migration
{
    public function up()
    {
        Schema::create('custom_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('name', 100)->unique();
            $table->mediumText('sql_statement')->unique();
            $table->longText('statement_results');
            $table->char('modified_by', 100);
            $table->dateTime('last_run');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_reports');
    }
}
