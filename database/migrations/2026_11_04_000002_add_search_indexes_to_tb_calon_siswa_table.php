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
        Schema::table('tb_calon_siswa', function (Blueprint $table) {
            $table->index('nik_calon_siswa', 'idx_calon_siswa_nik');
            $table->index('status_pendaftaran', 'idx_calon_siswa_status_pendaftaran');
            $table->index('tgl_pendaftaran', 'idx_calon_siswa_tgl_pendaftaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_calon_siswa', function (Blueprint $table) {
            $table->dropIndex('idx_calon_siswa_nik');
            $table->dropIndex('idx_calon_siswa_status_pendaftaran');
            $table->dropIndex('idx_calon_siswa_tgl_pendaftaran');
        });
    }
};

