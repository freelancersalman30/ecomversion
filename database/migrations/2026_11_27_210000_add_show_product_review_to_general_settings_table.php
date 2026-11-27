<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('general_settings')) {
            Schema::table('general_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('general_settings', 'show_product_review')) {
                    $table->tinyInteger('show_product_review')->default(1)->after('show_category_wise_products');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('general_settings')) {
            Schema::table('general_settings', function (Blueprint $table) {
                if (Schema::hasColumn('general_settings', 'show_product_review')) {
                    $table->dropColumn('show_product_review');
                }
            });
        }
    }
};
