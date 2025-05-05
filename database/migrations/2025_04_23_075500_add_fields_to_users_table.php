<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nic')->nullable()->unique()->after('name');
            $table->string('address')->nullable()->after('nic');
            $table->string('mobile')->nullable()->after('address');
            $table->string('gender')->nullable()->after('email');
            $table->unsignedBigInteger('territory_id')->nullable()->after('gender');
            $table->string('username')->nullable()->unique()->after('territory_id');
        });
    
        // Add foreign key constraint separately
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('territory_id')
                  ->references('id')
                  ->on('territories')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['territory_id']);
            $table->dropColumn(['nic', 'address', 'mobile', 'gender', 'territory_id', 'username']);
        });
    }
};