<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fee_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('section');
            $table->string('class');
            $table->string('arm')->nullable();
            $table->integer('class_id');
            $table->text('description');
            $table->float('amount');
            $table->boolean('is_optional')->default(true);
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
        Schema::dropIfExists('fee_configurations');
    }
}
