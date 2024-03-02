<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username',20);
            $table->string('email',50)->unique();
            $table->string('password');
            $table->string('profilepicture')->default('default.jpg');
            $table->string('userbio',250)->nullable();
            $table->boolean('isactive')->default(0); 
            $table->enum('usertype', ['user', 'admin'])->default('user');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
