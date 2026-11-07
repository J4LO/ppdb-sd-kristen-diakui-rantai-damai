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
        // 1. Tambahkan kolom no_registrasi secara nullable terlebih dahulu
        Schema::table('tb_calon_siswa', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_calon_siswa', 'no_registrasi')) {
                $table->string('no_registrasi', 30)->nullable()->after('id_tahun_ajaran');
            }
        });

        // 2. Isi data lama secara berurutan per tahun ajaran
        $tahunAjarans = DB::table('tb_tahun_ajaran')->get();
        $tahunMap = [];
        foreach ($tahunAjarans as $ta) {
            $tahunMap[$ta->id_tahun_ajaran] = $ta->tahun_ajaran;
        }

        // Ambil semua calon siswa yang belum punya no_registrasi, urutkan per tahun ajaran dan id
        $calonSiswas = DB::table('tb_calon_siswa')
            ->whereNull('no_registrasi')
            ->orderBy('id_tahun_ajaran', 'asc')
            ->orderBy('id_calon_siswa', 'asc')
            ->get();

        $counterPerTahun = [];

        foreach ($calonSiswas as $cs) {
            $idTa = $cs->id_tahun_ajaran ?? 1;
            $tahunStr = $tahunMap[$idTa] ?? date('Y') . '/' . (date('Y') + 1);

            preg_match_all('/\d+/', $tahunStr, $matches);
            if (!empty($matches[0]) && count($matches[0]) >= 2) {
                $y1 = substr($matches[0][0], -2);
                $y2 = substr($matches[0][1], -2);
                $tahunSingkat = $y1 . $y2;
            } else {
                $tahunSingkat = date('y') . (date('y') + 1);
            }

            if (!isset($counterPerTahun[$idTa])) {
                $counterPerTahun[$idTa] = 1;
            } else {
                $counterPerTahun[$idTa]++;
            }

            $urut = str_pad($counterPerTahun[$idTa], 4, '0', STR_PAD_LEFT);
            $noReg = "PPDB-{$tahunSingkat}-{$urut}";

            // Pastikan jika ada nomor yang sudah dipakai, lakukan iterasi
            while (DB::table('tb_calon_siswa')->where('no_registrasi', $noReg)->exists()) {
                $counterPerTahun[$idTa]++;
                $urut = str_pad($counterPerTahun[$idTa], 4, '0', STR_PAD_LEFT);
                $noReg = "PPDB-{$tahunSingkat}-{$urut}";
            }

            DB::table('tb_calon_siswa')
                ->where('id_calon_siswa', $cs->id_calon_siswa)
                ->update(['no_registrasi' => $noReg]);
        }

        // 3. Pasang indeks UNIQUE setelah seluruh data terisi
        Schema::table('tb_calon_siswa', function (Blueprint $table) {
            $table->unique('no_registrasi', 'tb_calon_siswa_no_registrasi_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_calon_siswa', function (Blueprint $table) {
            if (Schema::hasColumn('tb_calon_siswa', 'no_registrasi')) {
                $table->dropUnique('tb_calon_siswa_no_registrasi_unique');
                $table->dropColumn('no_registrasi');
            }
        });
    }
};

