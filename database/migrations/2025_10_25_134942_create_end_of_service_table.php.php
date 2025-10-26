<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('end_of_service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('termination_date')->nullable();
            $table->decimal('total_service_years', 8, 2)->nullable(); // calculated years
            $table->decimal('amount', 15, 2)->nullable(); // calculated EOS amount
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('end_of_service_records');
    }
};
