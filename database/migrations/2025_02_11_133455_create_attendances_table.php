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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('rfid_card');
            $table->timestamp('check_in');
            $table->timestamp('check_out')->nullable();
            $table->boolean('status', ['telat', 'masuk', 'izin']);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('rfid_card')->references('rfid_card')->on('rfid_cards')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
