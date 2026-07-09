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
        Schema::create('ben_transaction_details_main_2022_2023_s1', function (Blueprint $table) {
            $table->string('financial_year', 9);
            $table->integer('ben_id');
            $table->smallInteger('scheme_id');
            $table->integer('present_amt')->nullable()->default(0);
            $table->integer('present_count')->nullable()->default(0);
            $table->decimal('apr_lot_no', 6, 0)->nullable();
            $table->char('apr_lot_type', 1)->nullable()->default('R');
            $table->char('apr_lot_status', 1)->nullable()->default('R');
            $table->boolean('apr_is_eligible')->nullable()->default(true);
            $table->integer('apr_eligible_amount')->nullable()->default(0);
            $table->integer('apr_payment_amount')->nullable()->default(0);
            $table->decimal('may_lot_no', 6, 0)->nullable();
            $table->char('may_lot_type', 1)->nullable()->default('R');
            $table->char('may_lot_status', 1)->nullable()->default('R');
            $table->boolean('may_is_eligible')->nullable()->default(true);
            $table->integer('may_eligible_amount')->nullable()->default(0);
            $table->integer('may_payment_amount')->nullable()->default(0);
            $table->decimal('jun_lot_no', 6, 0)->nullable();
            $table->char('jun_lot_type', 1)->nullable()->default('R');
            $table->char('jun_lot_status', 1)->nullable()->default('R');
            $table->boolean('jun_is_eligible')->nullable()->default(true);
            $table->integer('jun_eligible_amount')->nullable()->default(0);
            $table->integer('jun_payment_amount')->nullable()->default(0);
            $table->decimal('jul_lot_no', 6, 0)->nullable();
            $table->char('jul_lot_type', 1)->nullable()->default('R');
            $table->char('jul_lot_status', 1)->nullable()->default('R');
            $table->boolean('jul_is_eligible')->nullable()->default(true);
            $table->integer('jul_eligible_amount')->nullable()->default(0);
            $table->integer('jul_payment_amount')->nullable()->default(0);
            $table->decimal('aug_lot_no', 6, 0)->nullable();
            $table->char('aug_lot_type', 1)->nullable()->default('R');
            $table->char('aug_lot_status', 1)->nullable()->default('R');
            $table->boolean('aug_is_eligible')->nullable()->default(true);
            $table->integer('aug_eligible_amount')->nullable()->default(0);
            $table->integer('aug_payment_amount')->nullable()->default(0);
            $table->decimal('sep_lot_no', 6, 0)->nullable();
            $table->char('sep_lot_type', 1)->nullable()->default('R');
            $table->char('sep_lot_status', 1)->nullable()->default('R');
            $table->boolean('sep_is_eligible')->nullable()->default(true);
            $table->integer('sep_eligible_amount')->nullable()->default(0);
            $table->integer('sep_payment_amount')->nullable()->default(0);
            $table->decimal('oct_lot_no', 6, 0)->nullable();
            $table->char('oct_lot_type', 1)->nullable()->default('R');
            $table->char('oct_lot_status', 1)->nullable()->default('R');
            $table->boolean('oct_is_eligible')->nullable()->default(true);
            $table->integer('oct_eligible_amount')->nullable()->default(0);
            $table->integer('oct_payment_amount')->nullable()->default(0);
            $table->decimal('nov_lot_no', 6, 0)->nullable();
            $table->char('nov_lot_type', 1)->nullable()->default('R');
            $table->char('nov_lot_status', 1)->nullable()->default('R');
            $table->boolean('nov_is_eligible')->nullable()->default(true);
            $table->integer('nov_eligible_amount')->nullable()->default(0);
            $table->integer('nov_payment_amount')->nullable()->default(0);
            $table->decimal('dec_lot_no', 6, 0)->nullable();
            $table->char('dec_lot_type', 1)->nullable()->default('R');
            $table->char('dec_lot_status', 1)->nullable()->default('R');
            $table->boolean('dec_is_eligible')->nullable()->default(true);
            $table->integer('dec_eligible_amount')->nullable()->default(0);
            $table->integer('dec_payment_amount')->nullable()->default(0);
            $table->decimal('jan_lot_no', 6, 0)->nullable();
            $table->char('jan_lot_type', 1)->nullable()->default('R');
            $table->char('jan_lot_status', 1)->nullable()->default('R');
            $table->boolean('jan_is_eligible')->nullable()->default(true);
            $table->integer('jan_eligible_amount')->nullable()->default(0);
            $table->integer('jan_payment_amount')->nullable()->default(0);
            $table->decimal('feb_lot_no', 6, 0)->nullable();
            $table->char('feb_lot_type', 1)->nullable()->default('R');
            $table->char('feb_lot_status', 1)->nullable()->default('R');
            $table->boolean('feb_is_eligible')->nullable()->default(true);
            $table->integer('feb_eligible_amount')->nullable()->default(0);
            $table->integer('feb_payment_amount')->nullable()->default(0);
            $table->decimal('mar_lot_no', 6, 0)->nullable();
            $table->char('mar_lot_type', 1)->nullable()->default('R');
            $table->char('mar_lot_status', 1)->nullable()->default('R');
            $table->boolean('mar_is_eligible')->nullable()->default(true);
            $table->integer('mar_eligible_amount')->nullable()->default(0);
            $table->integer('mar_payment_amount')->nullable()->default(0);

            $table->primary(['financial_year', 'scheme_id', 'ben_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ben_transaction_details_main_2022_2023_s1');
    }
};
