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
        Schema::create('user_time_trackers', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->date('date');
            $table->string('note')->nullable();
            $table->boolean('is_check_in')->default(0);
            $table->timestamp('tracked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_time_trackers');
    }
};
