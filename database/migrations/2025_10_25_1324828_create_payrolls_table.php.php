<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();

            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('gross_pay', 15, 2)->default(0); // basic + overtime + previous balance
            $table->decimal('net_pay', 15, 2)->default(0); // gross - deductions

            $table->decimal('previous_balance', 15, 2)->default(0); // unpaid salary from previous period
            $table->decimal('paid_amount', 15, 2)->default(0); // paid in this payroll
            $table->decimal('remaining_balance', 15, 2)->default(0); // unpaid amount carried forward

            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable(); // bank/transfer/cash
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payrolls');
    }
};
