<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreNurseryAffectivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pre_nursery_affectives', function (Blueprint $table) {
            $table->id();
            $table->string('alertness')->nullable();
            $table->string('appearance')->nullable();
            $table->string('independently')->nullable();
            $table->string('work')->nullable();
            $table->string('honesty')->nullable();
            $table->string('cooperation')->nullable();
            $table->string('politeness')->nullable();
            $table->string('confidence')->nullable();
            $table->string('speaking')->nullable();
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
        Schema::dropIfExists('pre_nursery_affectives');
    }
}
