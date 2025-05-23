<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('po_items', function (Blueprint $table) {
            $table->decimal('discounted_price', 10, 2);
            $table->decimal('discount', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         

        Schema::table('po_items', function (Blueprint $table) {
            $table->dropColumn('discounted_price');
            $table->dropColumn('discount');
        });
    }
};
