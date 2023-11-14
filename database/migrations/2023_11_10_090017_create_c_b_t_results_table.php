<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCBTResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_b_t_results', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->integer('subject_id');
            $table->string('student_id');
            $table->string('marks_obtainable');
            $table->integer('marks_obtained');
            $table->string('grade');
            $table->string('term');
            $table->string('session');
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
        Schema::dropIfExists('c_b_t_results');
    }
}
