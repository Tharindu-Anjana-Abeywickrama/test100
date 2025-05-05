<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreeIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('free_issues', function (Blueprint $table) {
            $table->id();
            $table->string('lable_name');
            $table->enum('free_issue_type', ['Flat', 'Multiple']);
            $table->foreignId('purches_product_id')->constrained('skus');
            $table->foreignId('free_product_id')->constrained('skus');
            $table->integer('purches_qty');
            $table->integer('free_qty');
            $table->foreignId('created_by')->constrained('users');
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
        Schema::dropIfExists('free_issues');
    }
}