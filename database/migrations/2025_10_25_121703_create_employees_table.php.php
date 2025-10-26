<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('address')->nullable();
            $table->string('image')->nullable();
            $table->string('nationality')->nullable();
            $table->string('iqama_number')->nullable();
            $table->date('iqama_expiry_date')->nullable();
            $table->string('job_title')->nullable();
            $table->string('salary')->nullable();
            $table->string('department')->nullable();
            $table->date('start_work_date')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('password')->unique();
            $table->string('phone')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('emer_phone_name')->nullable();
            $table->string('emergency_Phone')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
