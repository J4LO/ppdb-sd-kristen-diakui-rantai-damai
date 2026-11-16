<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('tb_daftar_ulang', 'status_berkas')) {
            Schema::table('tb_daftar_ulang', function (Blueprint $table) {
                $table->string('status_berkas', 50)->default('Menunggu Berkas')->after('status_daftar_ulang');
                $table->string('jenis_berkas', 50)->nullable()->after('status_berkas');
                $table->datetime('tgl_berkas_diterima')->nullable()->after('jenis_berkas');
                $table->string('penerima_berkas', 100)->nullable()->after('tgl_berkas_diterima');
                $table->text('catatan_berkas')->nullable()->after('penerima_berkas');
                $table->datetime('tgl_jatuh_tempo_du')->nullable()->after('catatan_berkas');
            });
        }

        DB::statement("ALTER TABLE tb_daftar_ulang MODIFY status_daftar_ulang VARCHAR(50) DEFAULT 'Menunggu Berkas'");

        if (!Schema::hasColumn('tb_pembayaran_daftar_ulang', 'no_kuitansi')) {
            Schema::table('tb_pembayaran_daftar_ulang', function (Blueprint $table) {
                $table->string('no_kuitansi', 100)->nullable()->after('waktu_lunas');
                $table->text('keterangan')->nullable()->after('no_kuitansi');
                $table->string('dicatat_oleh', 100)->nullable()->after('keterangan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tb_daftar_ulang', 'status_berkas')) {
            Schema::table('tb_daftar_ulang', function (Blueprint $table) {
                $table->dropColumn([
                    'status_berkas',
                    'jenis_berkas',
                    'tgl_berkas_diterima',
                    'penerima_berkas',
                    'catatan_berkas',
                    'tgl_jatuh_tempo_du',
                ]);
            });
        }

        if (Schema::hasColumn('tb_pembayaran_daftar_ulang', 'no_kuitansi')) {
            Schema::table('tb_pembayaran_daftar_ulang', function (Blueprint $table) {
                $table->dropColumn([
                    'no_kuitansi',
                    'keterangan',
                    'dicatat_oleh',
                ]);
            });
        }
    }
};

