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
        Schema::create('post', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('caption');
            $table->string('tags');
            $table->varChar('location',255);
            $table->datetime('scheduledAt');
            $table->enum('postType',['photo,video','text']);
            $table->integer('likes');
            $table->integer('shares');
            $tables->integer('comments');
            $table->integer('impressions');




            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post');
    }
};
