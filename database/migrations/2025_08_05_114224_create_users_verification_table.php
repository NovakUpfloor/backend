<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersVerificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_verification', function (Blueprint $table) {
            $table->bigIncrements('id_user'); // Primary key auto-increment
            $table->string('nama', 50);
            $table->string('email', 255);
            $table->string('username', 32);
            $table->string('password', 255); // Diperpanjang untuk hashing
            $table->string('gambar', 191)->nullable(); // Sesuai query asli
            $table->integer('paket_id'); // Kolom integer biasa, bukan primary key
            $table->string('token', 64)->unique(); // Token unik
            $table->timestamp('tanggal_expired');
            $table->timestamps(); // created_at dan updated_at
            $table->charset = 'utf8mb4'; // Set karakter
            $table->collation = 'utf8mb4_unicode_ci'; // Set kolasi
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_verification');
    }
}
