<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModifyStatusPembayaranInTransaksiPaketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE transaksi_paket MODIFY COLUMN status_pembayaran ENUM('pending','confirmed','rejected','unverified') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE transaksi_paket MODIFY COLUMN status_pembayaran ENUM('pending','confirmed','rejected') NOT NULL DEFAULT 'pending'");
    }
}
