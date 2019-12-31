<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConsultingMode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('consulting_mode')->default(0);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('assoc_client_id')->unsigned()->nullable();
            $table->foreign('assoc_client_id')->references('id')->on('clients')->onDelete('cascade');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->integer('assoc_client_id')->unsigned()->nullable();
            $table->foreign('assoc_client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->string('candidate_position', 255)->nullable();
            $table->integer('annual_target_salary')->nullable();
            $table->integer('fee_rate')->nullable();
            $table->integer('expense_rate')->nullable();
            $table->string('candidate_name', 255)->nullable();
            $table->date('signed_at')->nullable();
            $table->date('start_of_work')->nullable();
            $table->date('warranty_period_until')->nullable();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('assoc_client_id')->unsigned()->nullable();
            $table->foreign('assoc_client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->string('service_period', 255)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('consulting_mode');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['assoc_client_id']);
            $table->dropColumn('assoc_client_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['assoc_client_id']);
            $table->dropColumn('assoc_client_id');
            $table->dropColumn('candidate_position');
            $table->dropColumn('annual_target_salary');
            $table->dropColumn('fee_rate');
            $table->dropColumn('expense_rate');
            $table->dropColumn('candidate_name');
            $table->dropColumn('signed_at');
            $table->dropColumn('start_of_work');
            $table->dropColumn('warranty_period_until');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assoc_client_id']);
            $table->dropColumn('assoc_client_id');
            $table->dropColumn('service_period');
            $table->dropColumn('amount');
        });
    }
}
