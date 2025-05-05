<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('skus', function (Blueprint $table) {
            $table->string('weight_unit')->after('weight_volume');
        });
    }

    public function down()
    {
        Schema::table('skus', function (Blueprint $table) {
            $table->dropColumn('weight_unit');
        });
    }
};