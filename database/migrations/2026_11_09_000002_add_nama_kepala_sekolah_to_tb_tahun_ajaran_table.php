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
        Schema::table('tb_tahun_ajaran', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_tahun_ajaran', 'nama_kepala_sekolah')) {
                $table->string('nama_kepala_sekolah', 100)->nullable()->after('tgl_seleksi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_tahun_ajaran', function (Blueprint $table) {
            if (Schema::hasColumn('tb_tahun_ajaran', 'nama_kepala_sekolah')) {
                $table->dropColumn('nama_kepala_sekolah');
            }
        });
    }
};

