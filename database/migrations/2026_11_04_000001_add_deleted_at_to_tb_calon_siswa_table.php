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
            if (!Schema::hasColumn('tb_calon_siswa', 'deleted_at')) {
                $table->softDeletes()->after('catatan_pendaftaran');
                $table->index('deleted_at', 'idx_calon_siswa_deleted_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_calon_siswa', function (Blueprint $table) {
            if (Schema::hasColumn('tb_calon_siswa', 'deleted_at')) {
                $table->dropIndex('idx_calon_siswa_deleted_at');
                $table->dropSoftDeletes();
            }
        });
    }
};

