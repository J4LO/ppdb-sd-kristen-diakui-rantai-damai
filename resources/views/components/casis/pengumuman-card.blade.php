@php
    // Nama bulan Indonesia untuk format tanggal
    $bulanId = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $formatTgl = function($tgl) use ($bulanId) {
        if (!$tgl) return '-';
        $d = \Carbon\Carbon::parse($tgl);
        return $d->format('d') . ' ' . $bulanId[(int)$d->format('n')] . ' ' . $d->format('Y');
    };

    $today = \Carbon\Carbon::now()->startOfDay();
    $tglMulai = $tahunajar->mulai_pendaftaran ? \Carbon\Carbon::parse($tahunajar->mulai_pendaftaran)->startOfDay() : null;
    $tglBatas = $tahunajar->batas_pendaftaran ? \Carbon\Carbon::parse($tahunajar->batas_pendaftaran)->startOfDay() : null;
    $tglSeleksi = $tahunajar->tgl_seleksi ? \Carbon\Carbon::parse($tahunajar->tgl_seleksi)->startOfDay() : null;

    // Timeline Jadwal 3 Tahap
    $jadwalItems = [
        [
            'num'       => 1,
            'label'     => 'Pendaftaran Dibuka',
            'tgl'       => $formatTgl($tahunajar->mulai_pendaftaran),
            'jam'       => null,
            'status'    => ($tglMulai && $today->gte($tglMulai)) ? 'selesai' : 'belum',
            'relatif'   => ($tglMulai && $today->isSameDay($tglMulai)) ? 'Hari ini' : (($tglMulai && $today->gt($tglMulai)) ? 'Sudah dibuka' : 'Mendatang'),
        ],
        [
            'num'       => 2,
            'label'     => 'Pendaftaran Ditutup',
            'tgl'       => $formatTgl($tahunajar->batas_pendaftaran),
            'jam'       => null,
            'status'    => ($tglBatas && $today->gt($tglBatas)) ? 'selesai' : (($tglBatas && $today->between($tglMulai ?? $today, $tglBatas)) ? 'aktif' : 'belum'),
            'relatif'   => ($tglBatas && $today->gt($tglBatas)) ? 'Selesai' : (($tglBatas && $today->isSameDay($tglBatas)) ? 'Hari ini terakhir' : ($tglBatas ? 'sisa ' . $today->diffInDays($tglBatas) . ' hari' : '-')),
        ],
        [
            'num'       => 3,
            'label'     => 'Seleksi Observasi',
            'tgl'       => $formatTgl($tahunajar->tgl_seleksi),
            'jam'       => '08:00 – 10:00 ' . school_timezone_label(),
            'status'    => ($tglSeleksi && $today->gt($tglSeleksi)) ? 'selesai' : (($tglSeleksi && $today->isSameDay($tglSeleksi)) ? 'aktif' : (($tglBatas && $today->gt($tglBatas)) ? 'aktif' : 'belum')),
            'relatif'   => ($tglSeleksi && $today->gt($tglSeleksi)) ? 'Selesai' : (($tglSeleksi && $today->isSameDay($tglSeleksi)) ? 'Hari ini' : (($tglBatas && $today->gt($tglBatas)) ? 'Berikutnya' : 'Sesuai jadwal')),
        ],
    ];
@endphp

<div id="card-pengumuman" class="dash-card">

    {{-- Header Kartu Jadwal --}}
    <div class="dc-header shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <div class="dc-icon-wrap dc-icon-emerald shrink-0">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
            </div>
            <div class="min-w-0">
                <h3 class="dc-title">Jadwal PPDB</h3>
                <span class="dc-subtitle">Tanggal penting seleksi</span>
            </div>
        </div>
        {{-- Satu-satunya badge status PPDB di halaman --}}
        <span class="dc-badge dc-badge-emerald shrink-0">
            <span class="dc-badge-dot" aria-hidden="true"></span>
            {{ $tahunajar->status ?? 'Aktif' }}
        </span>
    </div>

    {{-- Body: Timeline Jadwal & Kotak Info --}}
    <div class="dc-body flex-1 flex flex-col justify-between">

        {{-- Timeline Jadwal Vertikal Rata Kiri --}}
        <ol class="dc-timeline flex-1" aria-label="Jadwal penting PPDB">
            @foreach($jadwalItems as $item)
                <li class="dc-tl-item {{ $loop->last ? 'dc-tl-item-last' : '' }}">

                    {{-- Rail: Badge Status + Garis Vertikal --}}
                    <div class="dc-tl-rail" aria-hidden="true">
                        @if($item['status'] === 'selesai')
                            <div class="dc-tl-badge dc-tl-done">
                                <i class="fas fa-check dc-tl-icon"></i>
                            </div>
                        @elseif($item['status'] === 'aktif')
                            <div class="dc-tl-badge dc-tl-active">
                                <span class="dc-tl-num">{{ $item['num'] }}</span>
                            </div>
                        @else
                            <div class="dc-tl-badge dc-tl-pending">
                                <span class="dc-tl-num">{{ $item['num'] }}</span>
                            </div>
                        @endif

                        @if(!$loop->last)
                            <div class="dc-tl-line {{ $item['status'] === 'selesai' ? 'dc-tl-line-done' : 'dc-tl-line-pending' }}"></div>
                        @endif
                    </div>

                    {{-- Konten Jadwal: Rata Kiri --}}
                    <div class="dc-tl-content">
                        <span class="sr-only">
                            {{ $item['label'] }}, status: {{ $item['status'] === 'selesai' ? 'selesai' : ($item['status'] === 'aktif' ? 'berikutnya' : 'belum') }}
                        </span>

                        {{-- Label Kegiatan --}}
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-semibold text-slate-600 leading-snug">
                                {{ $item['label'] }}
                            </span>
                            @if(!empty($item['relatif']))
                                <span class="dc-badge-tag {{ $item['status'] === 'selesai' ? 'dc-tag-done' : ($item['status'] === 'aktif' ? 'dc-tag-active' : 'dc-tag-pending') }}">
                                    {{ $item['relatif'] }}
                                </span>
                            @endif
                        </div>

                        {{-- Nilai Tanggal --}}
                        <p class="text-base sm:text-[17px] font-bold text-slate-900 leading-tight mt-1 mb-0">
                            {{ $item['tgl'] }}
                        </p>

                        {{-- Baris Jam (khusus seleksi) --}}
                        @if(!empty($item['jam']))
                            <p class="text-sm font-medium text-slate-500 flex items-center gap-1.5 mt-1 mb-0">
                                <i class="fas fa-clock text-xs text-purple-600" aria-hidden="true"></i>
                                <span>{{ $item['jam'] }}</span>
                            </p>
                        @endif
                    </div>

                </li>
            @endforeach
        </ol>

        {{-- Kotak Info Tempel di Dasar Kartu --}}
        <div class="dc-infobox mt-auto">
            <span class="dc-infobox-icon shrink-0" aria-hidden="true">
                <i class="fas fa-info-circle"></i>
            </span>
            <p class="dc-infobox-text">
                Unduh hasil seleksi pada menu <strong>Pengumuman</strong> setelah menyelesaikan observasi.
            </p>
        </div>

    </div>
</div>
