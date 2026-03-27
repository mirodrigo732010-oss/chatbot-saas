<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('time');
            $table->string('status')->default('scheduled');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
