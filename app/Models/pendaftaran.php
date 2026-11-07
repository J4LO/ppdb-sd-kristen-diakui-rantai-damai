<?php

namespace App\Models;

class pendaftaran extends CalonSiswa
{
    // Alias model untuk menjaga kompatibilitas codebase lama
    // Catatan: Pada struktur database baru, tb_pendaftaran telah dilebur ke dalam tb_calon_siswa.
    public function casis()
    {
        return $this;
    }
}
