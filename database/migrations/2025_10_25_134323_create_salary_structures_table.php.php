<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->json('allowances')->nullable(); // e.g. [{ "type": "housing", "amount": 500 }]
            $table->decimal('overtime_rate', 10, 2)->nullable(); // per hour
            $table->json('deductions')->nullable(); // e.g. [{"type":"loan","amount":100}]
            $table->string('salary_period')->default('monthly'); // monthly/biweekly etc.
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_structures');
    }
};
