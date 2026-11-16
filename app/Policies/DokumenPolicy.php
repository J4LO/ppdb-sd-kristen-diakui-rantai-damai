<?php

namespace App\Policies;

use App\Models\Dokumen;
use App\Models\User;

class DokumenPolicy
{
    /**
     * Menentukan apakah pengguna boleh melihat berkas dokumen.
     */
    public function view(User $user, Dokumen $dokumen): bool
    {
        // Admin dan Panitia berhak memeriksa dokumen pendaftaran
        if (in_array($user->role, ['Admin', 'Panitia'])) {
            return true;
        }

        // Orang Tua / Calon Siswa hanya berhak melihat dokumen miliknya sendiri
        if ($user->role === 'Orang Tua') {
            $casis = $user->calonSiswa ?? $user->casis;
            if ($casis && (int)$casis->id_calon_siswa === (int)$dokumen->id_calon_siswa) {
                return true;
            }
        }

        return false;
    }
}

