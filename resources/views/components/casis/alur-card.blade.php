@php
    $hasCasis = !empty($casis);
    $pembayaran = $casis?->pembayaran;
    $rawStatusBayar = strtolower($pembayaran?->status_pembayaran ?? '');
    $isLunasBayar = $pembayaran && in_array($rawStatusBayar, ['lunas', 'diterima', 'settlement', 'success', 'capture']);
    $seleksi = $casis?->seleksi;
    $hasSeleksi = $seleksi && !empty($seleksi->hasil_seleksi) && $seleksi->isDiumumkan();
    $isLulus = $hasSeleksi && in_array($seleksi->hasil_seleksi, ['Lulus', 'Lolos']);
    $daftarUlang = $seleksi?->daftarUlang ?? $casis?->daftarUlang;
    $totalLunasDu = $daftarUlang ? $daftarUlang->pembayaranDaftarUlang()->whereIn('status_du', ['Lunas', 'Diterima'])->sum('jml_bayar_du') : 0;
    $totalBiayaDu = (float) ($tahunajar?->biaya_daftar_ulang ?? 1000000);
    $isDaftarUlangSelesai = $daftarUlang && ($daftarUlang->status_daftar_ulang === 'Selesai' || $totalLunasDu >= $totalBiayaDu);

    // Tentukan tahap aktif (1-5)
    if (!$hasCasis) {
        $tahapAktif = 2;
    } elseif (!$isLunasBayar) {
        $tahapAktif = 3;
    } elseif (!$hasSeleksi) {
        $tahapAktif = 4;
    } else {
        $tahapAktif = 5;
    }

    $steps = [
        1 => [
            'num'    => 1,
            'title'  => 'Registrasi Akun',
            'desc'   => 'Akun portal calon siswa berhasil dibuat dan aktif.',
            'status' => 'selesai',
        ],
        2 => [
            'num'    => 2,
            'title'  => 'Isi Formulir & Dokumen',
            'desc'   => 'Lengkapi data diri calon siswa dan berkas persyaratan.',
            'status' => $hasCasis ? 'selesai' : ($tahapAktif == 2 ? 'aktif' : 'belum'),
        ],
        3 => [
            'num'    => 3,
            'title'  => 'Pembayaran Pendaftaran',
            'desc'   => 'Bayar biaya formulir pendaftaran secara online via Midtrans.',
            'status' => $isLunasBayar ? 'selesai' : ($tahapAktif == 3 ? 'aktif' : 'belum'),
        ],
        4 => [
            'num'    => 4,
            'title'  => 'Seleksi Observasi',
            'desc'   => 'Ikuti tes observasi offline di sekolah sesuai jadwal.',
            'status' => $hasSeleksi ? 'selesai' : ($tahapAktif == 4 ? 'aktif' : 'belum'),
        ],
        5 => [
            'num'    => 5,
            'title'  => 'Pengumuman & Daftar Ulang',
            'desc'   => $isDaftarUlangSelesai ? 'Administrasi daftar ulang selesai.' : 'Cek hasil observasi dan selesaikan administrasi daftar ulang.',
            'status' => ($hasSeleksi && $isLulus && $isDaftarUlangSelesai) ? 'selesai' : ($tahapAktif == 5 ? 'aktif' : 'belum'),
        ],
    ];
@endphp

<div id="card-alur" class="dash-card">

    {{-- Header Kartu Alur --}}
    <div class="dc-header shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <div class="dc-icon-wrap {{ $isDaftarUlangSelesai ? 'dc-icon-emerald' : 'dc-icon-blue' }} shrink-0">
                <i class="fas {{ $isDaftarUlangSelesai ? 'fa-check-circle' : 'fa-route' }}" aria-hidden="true"></i>
            </div>
            <div class="min-w-0">
                <h3 class="dc-title">Alur Pendaftaran Online</h3>
                <span class="dc-subtitle">Tahapan Proses Penerimaan Siswa</span>
            </div>
        </div>
        <span class="dc-badge {{ $isDaftarUlangSelesai ? 'dc-badge-emerald' : 'dc-badge-blue' }} shrink-0">
            {{ $isDaftarUlangSelesai ? 'Selesai' : 'Tahap ' . $tahapAktif . ' dari 5' }}
        </span>
    </div>

    {{-- Body: Timeline 5 Langkah Mengisi Penuh Tinggi Kartu --}}
    <div class="dc-body flex-1 flex flex-col justify-between">

        <ol class="dc-timeline flex-1" aria-label="Alur pendaftaran">
            @foreach($steps as $step)
                <li class="dc-tl-item {{ $loop->last ? 'dc-tl-item-last' : '' }}">

                    {{-- Rail: Badge + Garis Vertikal --}}
                    <div class="dc-tl-rail" aria-hidden="true">
                        @if($step['status'] === 'selesai')
                            <div class="dc-tl-badge dc-tl-done">
                                <i class="fas fa-check dc-tl-icon"></i>
                            </div>
                        @elseif($step['status'] === 'aktif')
                            <div class="dc-tl-badge dc-tl-active">
                                <span class="dc-tl-num">{{ $step['num'] }}</span>
                            </div>
                        @else
                            <div class="dc-tl-badge dc-tl-pending">
                                <span class="dc-tl-num">{{ $step['num'] }}</span>
                            </div>
                        @endif

                        @if(!$loop->last)
                            <div class="dc-tl-line {{ $step['status'] === 'selesai' ? 'dc-tl-line-done' : 'dc-tl-line-pending' }}"></div>
                        @endif
                    </div>

                    {{-- Konten Langkah: Rata Kiri --}}
                    <div class="dc-tl-content">
                        <span class="sr-only">Langkah {{ $step['num'] }},
                            @if($step['status'] === 'selesai') selesai
                            @elseif($step['status'] === 'aktif') sedang berjalan
                            @else belum dilakukan
                            @endif:
                        </span>
                        <div class="dc-tl-title-row">
                            <h5 class="dc-tl-title
                                @if($step['status'] === 'aktif') dc-tl-title-active
                                @elseif($step['status'] === 'selesai') dc-tl-title-done
                                @else dc-tl-title-pending
                                @endif">
                                {{ $step['title'] }}
                            </h5>
                            @if($step['status'] === 'selesai')
                                <i class="fas fa-check-circle dc-tl-done-icon" aria-hidden="true"></i>
                            @endif
                        </div>
                        <p class="dc-tl-desc">{{ $step['desc'] }}</p>
                    </div>

                </li>
            @endforeach
        </ol>

    </div>
</div>
