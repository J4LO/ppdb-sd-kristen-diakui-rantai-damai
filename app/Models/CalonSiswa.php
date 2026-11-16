<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CalonSiswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tb_calon_siswa';
    protected $primaryKey = 'id_calon_siswa';
    public $timestamps = false;

    protected $fillable = [
        'id_calon_siswa',
        'id_user',
        'id_tahun_ajaran',
        'no_registrasi',
        'nik_calon_siswa',
        'nama_calon_siswa',
        'tmpt_lahir',
        'tgl_lahir',
        'agama',
        'jmlh_saudara',
        'jenis_kelamin',
        'status_anak',
        'alamat_calon_siswa',
        'no_hp_calon_siswa',
        'nama_ayah',
        'nama_ibu',
        'pendidikan_ayah',
        'pendidikan_ibu',
        'pekerjaan_ayah',
        'pekerjaan_ibu',
        'penghasilan_ayah',
        'penghasilan_ibu',
        'no_hp_orang_tua',
        'tgl_pendaftaran',
        'status_pendaftaran',
        'catatan_pendaftaran',
    ];

    protected $casts = [
        'tgl_lahir' => 'date',
        'tgl_pendaftaran' => 'date',
        'jmlh_saudara' => 'integer',
        'penghasilan_ayah' => 'integer',
        'penghasilan_ibu' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->no_registrasi)) {
                $model->no_registrasi = static::generateNoRegistrasi($model->id_tahun_ajaran);
            }
        });
    }

    /**
     * Generate sequential unique registration number inside a DB transaction with lock.
     * Format: PPDB-{tahun_singkat}-{urut_4_digit} (e.g. PPDB-2627-0001)
     */
    public static function generateNoRegistrasi(?int $idTahunAjaran = null): string
    {
        return DB::transaction(function () use ($idTahunAjaran) {
            $idTa = $idTahunAjaran;
            if (!$idTa) {
                $tahunAktif = DB::table('tb_tahun_ajaran')->where('status', 'Aktif')->first()
                    ?? DB::table('tb_tahun_ajaran')->first();
                $idTa = $tahunAktif ? $tahunAktif->id_tahun_ajaran : 1;
            }

            $ta = DB::table('tb_tahun_ajaran')->where('id_tahun_ajaran', $idTa)->first();
            $tahunStr = $ta ? $ta->tahun_ajaran : date('Y') . '/' . (date('Y') + 1);

            preg_match_all('/\d+/', $tahunStr, $matches);
            if (!empty($matches[0]) && count($matches[0]) >= 2) {
                $y1 = substr($matches[0][0], -2);
                $y2 = substr($matches[0][1], -2);
                $tahunSingkat = $y1 . $y2;
            } else {
                $tahunSingkat = date('y') . (date('y') + 1);
            }

            $prefix = "PPDB-{$tahunSingkat}-";

            // Gunakan lockForUpdate untuk mencegah race condition
            $lastRecord = DB::table('tb_calon_siswa')
                ->where('no_registrasi', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderByRaw('CAST(SUBSTRING(no_registrasi, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
                ->first();

            $lastNumber = 0;
            if ($lastRecord && preg_match('/' . preg_quote($prefix, '/') . '(\d+)/', $lastRecord->no_registrasi, $m)) {
                $lastNumber = (int)$m[1];
            }

            $newNumber = $lastNumber + 1;
            $noReg = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            // Double check uniqueness defensively
            while (DB::table('tb_calon_siswa')->where('no_registrasi', $noReg)->exists()) {
                $newNumber++;
                $noReg = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
            }

            return $noReg;
        });
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Relasi ke Tahun Ajaran
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    // Alias relasi ke tahunajar
    public function tahunajar()
    {
        return $this->tahunAjaran();
    }

    // Relasi ke Berkas Dokumen
    public function dokumen()
    {
        return $this->hasMany(Dokumen::class, 'id_calon_siswa', 'id_calon_siswa');
    }

    // Relasi ke Pembayaran Formulir / Pendaftaran
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'id_calon_siswa', 'id_calon_siswa');
    }

    // Relasi ke Seleksi melalui Pembayaran
    public function seleksi()
    {
        return $this->hasOneThrough(
            Seleksi::class,
            Pembayaran::class,
            'id_calon_siswa', // Foreign key di tb_pembayaran
            'id_pembayaran',  // Foreign key di tb_seleksi
            'id_calon_siswa', // Local key di tb_calon_siswa
            'id_pembayaran'   // Local key di tb_pembayaran
        );
    }

    // Accessor ke Daftar Ulang melalui Seleksi
    public function getDaftarUlangAttribute()
    {
        return $this->seleksi?->daftarUlang;
    }

    /**
     * Scope calon siswa yang memenuhi syarat kelayakan seleksi:
     * - Pembayaran formulir pendaftaran Lunas / Diterima
     * - Terdaftar pada tahun ajaran aktif (jika parameter tahun ajar diberikan)
     * - Data tidak terarsip / soft deleted (bawaan SoftDeletes)
     */
    public function scopeLayakSeleksi($query, $tahunAjarId = null)
    {
        $query->whereHas('pembayaran', function ($q) {
            $q->whereIn('status_pembayaran', ['Lunas', 'Diterima']);
        });

        if ($tahunAjarId) {
            $query->where('id_tahun_ajaran', $tahunAjarId);
        }

        return $query;
    }

    // Accessors untuk kompatibilitas kode lama
    public function getNamaAttribute()
    {
        return $this->nama_calon_siswa;
    }

    public function getNikAttribute()
    {
        return $this->nik_calon_siswa;
    }

    public function getTempatLahirAttribute()
    {
        return $this->tmpt_lahir;
    }

    public function getTanggalLahirAttribute()
    {
        return $this->tgl_lahir;
    }

    public function getAlamatAttribute()
    {
        return $this->alamat_calon_siswa;
    }

    public function getNoHpAttribute()
    {
        return $this->no_hp_orang_tua ?: $this->no_hp_calon_siswa;
    }

    public function getJmlSaudaraAttribute()
    {
        return $this->jmlh_saudara;
    }

    public function getIdCasisAttribute()
    {
        return $this->id_calon_siswa;
    }

    public function getIdPendaftaranAttribute()
    {
        return $this->id_calon_siswa;
    }

    public function getCasisAttribute()
    {
        return $this;
    }

    public function getUserIdAttribute()
    {
        return $this->id_user;
    }

    public function getNamaOrtuAttribute()
    {
        return $this->nama_ayah ?: $this->nama_ibu;
    }

    public function getPendidikanOrtuAttribute()
    {
        return $this->pendidikan_ayah ?: $this->pendidikan_ibu;
    }

    public function getPekerjaanOrtuAttribute()
    {
        return $this->pekerjaan_ayah ?: $this->pekerjaan_ibu;
    }

    public function getGajiOrtuAttribute()
    {
        return $this->penghasilan_ayah ?: $this->penghasilan_ibu;
    }

    public function getTempatLahirOrtuAttribute()
    {
        return '-';
    }

    public function getTanggalLahirOrtuAttribute()
    {
        return null;
    }

    public function getStatusAttribute()
    {
        return $this->status_pendaftaran;
    }

    public function getPendaftaranAttribute()
    {
        return $this;
    }

    public function getAkteAttribute()
    {
        $doc = $this->dokumen->firstWhere('jenis_dokumen', 'Akta Kelahiran');
        return $doc ? basename($doc->file_path) : null;
    }

    public function getKkAttribute()
    {
        $doc = $this->dokumen->firstWhere('jenis_dokumen', 'Kartu Keluarga');
        return $doc ? basename($doc->file_path) : null;
    }

    public function getFotoAttribute()
    {
        $doc = $this->dokumen->firstWhere('jenis_dokumen', 'Pas Foto');
        return $doc ? basename($doc->file_path) : null;
    }
}

