<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tb_tahun_ajaran', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_tahun_ajaran', 'biaya_pembangunan')) {
                $table->decimal('biaya_pembangunan', 12, 2)->default(0)->after('biaya_pendaftaran');
            }
            if (!Schema::hasColumn('tb_tahun_ajaran', 'biaya_buku')) {
                $table->decimal('biaya_buku', 12, 2)->default(0)->after('biaya_pembangunan');
            }
            if (!Schema::hasColumn('tb_tahun_ajaran', 'biaya_seragam')) {
                $table->decimal('biaya_seragam', 12, 2)->default(0)->after('biaya_buku');
            }
        });

        // Inisialisasi data eksisting yang sudah memiliki biaya_daftar_ulang
        $rows = DB::table('tb_tahun_ajaran')->get();
        foreach ($rows as $row) {
            $total = (float) ($row->biaya_daftar_ulang ?? 0);
            if ($total > 0 && ((float)$row->biaya_pembangunan <= 0 && (float)$row->biaya_buku <= 0 && (float)$row->biaya_seragam <= 0)) {
                $pembangunan = round($total * 0.60, 2);
                $buku = round($total * 0.20, 2);
                $seragam = $total - $pembangunan - $buku;

                DB::table('tb_tahun_ajaran')
                    ->where('id_tahun_ajaran', $row->id_tahun_ajaran)
                    ->update([
                        'biaya_pembangunan' => $pembangunan,
                        'biaya_buku' => $buku,
                        'biaya_seragam' => $seragam,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_tahun_ajaran', function (Blueprint $table) {
            if (Schema::hasColumn('tb_tahun_ajaran', 'biaya_pembangunan')) {
                $table->dropColumn('biaya_pembangunan');
            }
            if (Schema::hasColumn('tb_tahun_ajaran', 'biaya_buku')) {
                $table->dropColumn('biaya_buku');
            }
            if (Schema::hasColumn('tb_tahun_ajaran', 'biaya_seragam')) {
                $table->dropColumn('biaya_seragam');
            }
        });
    }
};

