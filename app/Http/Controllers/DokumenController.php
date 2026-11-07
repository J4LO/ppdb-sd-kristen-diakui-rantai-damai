<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DokumenController extends Controller
{
    /**
     * Menampilkan berkas dokumen secara aman untuk pengguna terotorisasi.
     */
    public function show(Dokumen $dokumen)
    {
        // Otorisasi melalui DokumenPolicy
        $this->authorize('view', $dokumen);

        $path = $dokumen->file_path;

        // Cek keberadaan file di disk local
        if (!Storage::disk('local')->exists($path)) {
            // Fallback backward compatibility jika file masih berada di public
            if (Storage::disk('public')->exists($path)) {
                $fileContent = Storage::disk('public')->get($path);
                $mimeType = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';
                $size = Storage::disk('public')->size($path);
            } else {
                abort(404, 'File dokumen tidak ditemukan di penyimpanan server.');
            }
        } else {
            $fileContent = Storage::disk('local')->get($path);
            $mimeType = Storage::disk('local')->mimeType($path) ?: 'application/octet-stream';
            $size = Storage::disk('local')->size($path);
        }

        $filename = basename($path);

        $headers = [
            'Content-Type' => $mimeType,
            'Content-Length' => $size,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        return response($fileContent, 200, $headers);
    }
}

