<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom nullable waktu_dibuat dan channel pada tb_pembayaran.
     */
    public function up(): void
    {
        Schema::table('tb_pembayaran', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_pembayaran', 'waktu_dibuat')) {
                $table->dateTime('waktu_dibuat')->nullable()->after('tgl_pembayaran');
            }
            if (!Schema::hasColumn('tb_pembayaran', 'channel')) {
                $table->string('channel', 50)->nullable()->after('metode_pembayaran');
            }
        });

        // Inisialisasi waktu_dibuat dari tgl_pembayaran untuk rekaman yang sudah ada
        DB::table('tb_pembayaran')
            ->whereNull('waktu_dibuat')
            ->whereNotNull('tgl_pembayaran')
            ->update(['waktu_dibuat' => DB::raw('tgl_pembayaran')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_pembayaran', function (Blueprint $table) {
            if (Schema::hasColumn('tb_pembayaran', 'channel')) {
                $table->dropColumn('channel');
            }
            if (Schema::hasColumn('tb_pembayaran', 'waktu_dibuat')) {
                $table->dropColumn('waktu_dibuat');
            }
        });
    }
};

