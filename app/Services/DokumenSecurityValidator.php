<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class DokumenSecurityValidator
{
    /**
     * Memvalidasi file berkas dokumen secara ketat.
     *
     * @param UploadedFile $file
     * @param string $docType (akte, kk, foto, kps)
     * @throws ValidationException
     */
    public static function validate(UploadedFile $file, string $docType): void
    {
        $realPath = $file->getRealPath();
        if (!$realPath || !file_exists($realPath)) {
            throw ValidationException::withMessages([
                $docType => 'Berkas tidak valid atau gagal dibaca dari server.',
            ]);
        }

        // Dapatkan MIME type sebenarnya menggunakan Fileinfo (bukan dari client header)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($realPath);
        $fileSize = $file->getSize(); // Dalam bytes

        if ($docType === 'foto') {
            // Pas Foto 3x4: Wajib JPG, JPEG, atau PNG, Max 2048 KB (2 MB)
            $allowedMimes = ['image/jpeg', 'image/png'];
            if (!in_array($realMime, $allowedMimes)) {
                throw ValidationException::withMessages([
                    'foto' => 'Pas foto harus berformat JPG, JPEG, atau PNG.',
                ]);
            }

            if ($fileSize > 2048 * 1024) {
                throw ValidationException::withMessages([
                    'foto' => 'Ukuran pas foto maksimal 2 MB.',
                ]);
            }

            // Validasi header image integrity
            if (!@getimagesize($realPath)) {
                throw ValidationException::withMessages([
                    'foto' => 'Berkas pas foto rusak atau bukan gambar yang valid.',
                ]);
            }
        } else {
            // Akta Kelahiran, Kartu Keluarga, dan KPS: Wajib PDF, Max 5120 KB (5 MB)
            if ($realMime !== 'application/pdf') {
                throw ValidationException::withMessages([
                    $docType => 'Dokumen harus berformat PDF, maksimal 5 MB.',
                ]);
            }

            if ($fileSize > 5120 * 1024) {
                throw ValidationException::withMessages([
                    $docType => 'Ukuran dokumen PDF maksimal 5 MB.',
                ]);
            }

            // Periksa magic header tanda awal "%PDF-"
            $handle = fopen($realPath, 'rb');
            $header = $handle ? fread($handle, 5) : '';
            if ($handle) {
                fclose($handle);
            }

            if ($header !== '%PDF-') {
                throw ValidationException::withMessages([
                    $docType => 'Berkas yang diunggah bukan dokumen PDF yang valid.',
                ]);
            }

            // Deteksi sederhana konten berbahaya / JavaScript di dalam PDF
            $contentSample = @file_get_contents($realPath, false, null, 0, 1024 * 1024); // Baca 1MB pertama
            if ($contentSample !== false) {
                if (preg_match('/\/JavaScript\b|\/JS\b/i', $contentSample)) {
                    throw ValidationException::withMessages([
                        $docType => 'Dokumen PDF terdeteksi mengandung skrip otomatis/JavaScript dan ditolak demi keamanan.',
                    ]);
                }
                if (preg_match('/\/Encrypt\b/i', $contentSample)) {
                    throw ValidationException::withMessages([
                        $docType => 'Dokumen PDF terproteksi kata sandi (terenkripsi). Harap unggah PDF tanpa kata sandi.',
                    ]);
                }
            }
        }
    }
}

