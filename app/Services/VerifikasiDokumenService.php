<?php

namespace App\Services;

use App\Models\CalonSiswa;
use App\Models\Dokumen;
use App\Models\LogVerifikasiDokumen;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VerifikasiDokumenService
{
    public const REQUIRED_DOCUMENTS = [
        'Akta Kelahiran',
        'Kartu Keluarga',
        'Pas Foto',
    ];

    /**
     * Memverifikasi satu berkas dokumen calon siswa oleh Admin.
     *
     * @param Dokumen $dokumen
     * @param string $status 'Valid' | 'Tidak Valid'
     * @param string|null $catatan Wajib diisi jika status 'Tidak Valid'
     * @param User $admin
     * @return array
     */
    public function verifikasiDokumen(Dokumen $dokumen, string $status, ?string $catatan, User $admin): array
    {
        if (!in_array($status, ['Valid', 'Tidak Valid'])) {
            throw new InvalidArgumentException("Status verifikasi harus 'Valid' atau 'Tidak Valid'.");
        }

        $trimmedCatatan = $catatan !== null ? trim($catatan) : null;
        if ($status === 'Tidak Valid' && empty($trimmedCatatan)) {
            throw new InvalidArgumentException("Alasan penolakan dokumen wajib diisi.");
        }

        return DB::transaction(function () use ($dokumen, $status, $trimmedCatatan, $admin) {
            $statusLama = $dokumen->status_verifikasi;

            $dokumen->status_verifikasi = $status;
            $dokumen->catatan = ($status === 'Tidak Valid') ? $trimmedCatatan : null;
            $dokumen->save();

            // Catat log audit verifikasi
            LogVerifikasiDokumen::create([
                'id_dokumen'     => $dokumen->id_dokumen,
                'id_calon_siswa' => $dokumen->id_calon_siswa,
                'id_user_admin'  => $admin->id_user,
                'status_lama'    => $statusLama,
                'status_baru'    => $status,
                'catatan'        => ($status === 'Tidak Valid') ? $trimmedCatatan : 'Dokumen dinyatakan valid.',
                'created_at'     => now(),
            ]);

            // Sinkronkan status pendaftaran siswa secara otomatis
            $casis = $this->sinkronisasiStatusPendaftaran($dokumen->id_calon_siswa);

            return [
                'dokumen' => $dokumen,
                'casis'   => $casis,
            ];
        });
    }

    /**
     * Menghitung ulang dan menyinkronkan status pendaftaran calon siswa berdasarkan status dokumen-dokumennya.
     *
     * Aturan:
     * 1. Jika ada MINIMAL 1 dokumen berstatus 'Tidak Valid', maka status pendaftaran = 'Ditolak' ('Perlu Perbaikan')
     *    dan catatan_pendaftaran berisi rangkuman catatan dokumen yang ditolak.
     * 2. Jika SEMUA dokumen wajib (Akta, KK, Foto) ada dan berstatus 'Valid', maka status pendaftaran = 'Diterima' ('Data Terverifikasi')
     *    dan catatan_pendaftaran = null.
     * 3. Jika belum lengkap atau masih ada yang 'Menunggu' (dan tidak ada yang ditolak), maka status pendaftaran = 'Menunggu'.
     *
     * Catatan: Pembayaran TIDAK menjadi syarat status pendaftaran.
     *
     * @param int $idCalonSiswa
     * @return CalonSiswa
     */
    public function sinkronisasiStatusPendaftaran(int $idCalonSiswa): CalonSiswa
    {
        $casis = CalonSiswa::with('dokumen')->findOrFail($idCalonSiswa);
        $dokumens = $casis->dokumen;

        // 1. Cek apakah ada dokumen yang ditolak (Tidak Valid)
        $rejectedDocs = $dokumens->where('status_verifikasi', 'Tidak Valid');
        if ($rejectedDocs->isNotEmpty()) {
            $reasons = [];
            foreach ($rejectedDocs as $doc) {
                $reasonText = !empty($doc->catatan) ? $doc->catatan : 'Perlu diperbaiki';
                $reasons[] = "{$doc->jenis_dokumen}: {$reasonText}";
            }

            $casis->status_pendaftaran = 'Ditolak';
            $casis->catatan_pendaftaran = implode('; ', $reasons);
            $casis->save();

            return $casis;
        }

        // 2. Cek apakah semua dokumen wajib sudah berstatus 'Valid'
        $allRequiredValid = true;
        foreach (self::REQUIRED_DOCUMENTS as $reqType) {
            $found = $dokumens->firstWhere('jenis_dokumen', $reqType);
            if (!$found || $found->status_verifikasi !== 'Valid') {
                $allRequiredValid = false;
                break;
            }
        }

        if ($allRequiredValid) {
            $casis->status_pendaftaran = 'Diterima';
            $casis->catatan_pendaftaran = null;
            $casis->save();

            return $casis;
        }

        // 3. Masih dalam proses / ada yang menunggu
        $casis->status_pendaftaran = 'Menunggu';
        $casis->catatan_pendaftaran = null;
        $casis->save();

        return $casis;
    }

    /**
     * Override manual status pendaftaran oleh Admin dengan alasan wajib.
     *
     * @param CalonSiswa $casis
     * @param string $statusBaru 'Menunggu' | 'Diterima' | 'Ditolak'
     * @param string $alasan
     * @param User $admin
     * @return CalonSiswa
     */
    public function overrideStatusPendaftaran(CalonSiswa $casis, string $statusBaru, string $alasan, User $admin): CalonSiswa
    {
        if (!in_array($statusBaru, ['Menunggu', 'Diterima', 'Ditolak'])) {
            throw new InvalidArgumentException("Status pendaftaran harus 'Menunggu', 'Diterima', atau 'Ditolak'.");
        }

        $trimmedAlasan = trim($alasan);
        if (empty($trimmedAlasan)) {
            throw new InvalidArgumentException("Alasan override status pendaftaran wajib diisi.");
        }

        return DB::transaction(function () use ($casis, $statusBaru, $trimmedAlasan, $admin) {
            $statusLama = $casis->status_pendaftaran;

            $casis->status_pendaftaran = $statusBaru;
            if ($statusBaru === 'Ditolak') {
                $casis->catatan_pendaftaran = $trimmedAlasan;
            } else {
                $casis->catatan_pendaftaran = null;
            }
            $casis->save();

            // Catat ke log audit (id_dokumen null untuk override level siswa)
            LogVerifikasiDokumen::create([
                'id_dokumen'     => null,
                'id_calon_siswa' => $casis->id_calon_siswa,
                'id_user_admin'  => $admin->id_user,
                'status_lama'    => $statusLama,
                'status_baru'    => $statusBaru,
                'catatan'        => 'Manual Override Admin: ' . $trimmedAlasan,
                'created_at'     => now(),
            ]);

            return $casis;
        });
    }

    /**
     * Menghitung jumlah calon siswa aktif yang memiliki setidaknya 1 dokumen berstatus 'Menunggu'.
     *
     * @return int
     */
    public function hitungAntreanMenunggu(): int
    {
        return CalonSiswa::whereNull('deleted_at')
            ->whereHas('dokumen', function ($q) {
                $q->where('status_verifikasi', 'Menunggu');
            })
            ->count();
    }
}

