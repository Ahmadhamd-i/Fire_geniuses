<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('type')->nullable(); // e.g., driving
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->nullable(); // fine amount
            $table->date('date')->nullable();
            $table->json('evidence')->nullable(); // URLs, file refs
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('violations');
    }
};
