@extends('layout.tampilan')

@section('title', 'Detail Administrasi Daftar Ulang — ' . $item->casis->nama_calon_siswa)

{{-- Topbar breadcrumb: segmen tengah berupa tautan abu gelap, segmen aktif hijau tebal --}}
@section('page-title')
    <a href="{{ route('admin.daftar_ulang.index') }}" class="text-slate-600 hover:text-slate-900 hover:underline focus:underline focus:outline-none transition-colors">Daftar Ulang &amp; Uang Pangkal</a>
    <i class="fas fa-chevron-right text-[10px] text-slate-400 mx-2" aria-hidden="true"></i>
    <span class="text-emerald-700 font-bold">{{ $item->casis->nama_calon_siswa }}</span>
@endsection

@push('styles')
<style>
    /* Scoped Styles untuk Halaman Detail Daftar Ulang */
    .du-detail-page {
        container-type: inline-size;
        container-name: du-page;
    }

    .du-detail-page,
    .du-detail-page h1, .du-detail-page h2, .du-detail-page h3,
    .du-detail-page h4, .du-detail-page p, .du-detail-page span,
    .du-detail-page dt, .du-detail-page dd, .du-detail-page a,
    .du-detail-page td, .du-detail-page th,
    .du-detail-page button, .du-detail-page select, .du-detail-page input,
    .du-detail-page textarea {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
    }

    /* Grid Ringkasan: 3 Kolom saat ruang konten >= 650px; 1 Kolom di bawahnya */
    .du-summary-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    @container du-page (min-width: 650px) {
        .du-summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @supports not (container-type: inline-size) {
        @media (min-width: 700px) {
            .du-summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
    }

    /* Wadah Grid Berpasangan: 
       Saat ruang konten >= 960px (pada desktop >= 1250px dengan sidebar admin):
       Grid 2 Kolom (3fr 2fr) dengan align-items: stretch.
       Baris 1 = Rincian Tagihan (col 1, row 1) & Penerimaan Berkas (col 2, row 1)
       Baris 2 = Riwayat Setoran (col 1, row 2) & Pelunasan Tahap 2 (col 2, row 2)
       Kedua pasangan otomatis memiliki tinggi yang persis sama tanpa tinggi tetap (CSS Grid stretch).
       Saat ruang konten < 960px (termasuk layar 1024px dengan sidebar 256px dan mobile/tablet):
       1 Kolom berurutan: Tagihan -> Riwayat -> Berkas -> Tahap 2 (bebas scroll horizontal).
    */
    .du-grid-paired {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }
    @container du-page (min-width: 960px) {
        .du-grid-paired {
            display: grid;
            grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
            gap: 24px;
            align-items: stretch;
        }
        .du-card-tagihan {
            grid-column: 1;
            grid-row: 1;
        }
        .du-card-berkas {
            grid-column: 2;
            grid-row: 1;
        }
        .du-card-riwayat {
            grid-column: 1;
            grid-row: 2;
        }
        .du-card-tahap2 {
            grid-column: 2;
            grid-row: 2;
        }
    }
    @supports not (container-type: inline-size) {
        @media (min-width: 1250px) {
            .du-grid-paired {
                display: grid;
                grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
                gap: 24px;
                align-items: stretch;
            }
            .du-card-tagihan {
                grid-column: 1;
                grid-row: 1;
            }
            .du-card-berkas {
                grid-column: 2;
                grid-row: 1;
            }
            .du-card-riwayat {
                grid-column: 1;
                grid-row: 2;
            }
            .du-card-tahap2 {
                grid-column: 2;
                grid-row: 2;
            }
        }
    }
</style>
@endpush

@section('content')
@php
    $casis = $item->casis;
    $nama = $casis->nama_calon_siswa;
    $noReg = $casis->no_registrasi ?: '-';
    $ta = $casis->tahunAjaran;
    $taLabel = $ta?->tahun_ajaran ?: ($ta?->tahun_ajar ?: null);
    $jk = $casis->jenis_kelamin;
    $hpOrtu = $casis->no_hp_orang_tua ?: ($casis->user?->no_hp ?: null);
    $waNumber = $hpOrtu ? preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $hpOrtu)) : null;
@endphp

<div class="du-detail-page p-4 sm:p-6 max-w-7xl mx-auto space-y-6"
     x-data="{
         modalBayar: false,
         modalBerkas: false
     }">

    {{-- 1. Header Halaman: Tautan Balik, Judul Nama Siswa (28px bold), Meta Info, Tombol Aksi --}}
    <div class="space-y-2">
        {{-- Tautan Teks Kembali ke Daftar (Area Sentuh Minimum 44px) --}}
        <div>
            <a href="{{ route('admin.daftar_ulang.index') }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-800 hover:text-emerald-950 transition-colors py-2 pr-3 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 min-h-[44px]">
                <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                <span>Daftar Ulang</span>
            </a>
        </div>

        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="space-y-1.5">
                <h1 class="text-2xl sm:text-[28px] font-bold text-slate-900 tracking-tight leading-tight">
                    {{ $nama }}
                </h1>

                {{-- Baris Info Siswa: Badge Jenis Kelamin, Tahun Ajaran, No. Registrasi (Monospace + Tombol Salin), WhatsApp Ortu --}}
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                    @if($jk)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                            {{ $jk }}
                        </span>
                    @endif

                    @if($taLabel)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <i class="fas fa-calendar-alt text-[10px]" aria-hidden="true"></i>
                            <span>TA {{ $taLabel }}</span>
                        </span>
                    @endif

                    {{-- No. Registrasi Monospace dengan Tombol Salin --}}
                    <div class="inline-flex items-center gap-1.5 bg-slate-100 border border-slate-200 px-2.5 py-0.5 rounded-lg font-mono text-xs font-bold text-slate-700"
                         x-data="{ copiedReg: false }">
                        <span>{{ $noReg }}</span>
                        <button type="button"
                                @click="navigator.clipboard.writeText('{{ $noReg }}'); copiedReg = true; setTimeout(() => copiedReg = false, 2000)"
                                class="text-slate-400 hover:text-slate-700 p-0.5 rounded focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-emerald-600 cursor-pointer"
                                :title="copiedReg ? 'Tersalin!' : 'Salin Nomor Registrasi'"
                                aria-label="Salin Nomor Registrasi">
                            <i :class="copiedReg ? 'fas fa-check text-emerald-600' : 'fas fa-copy'" class="text-[11px]" aria-hidden="true"></i>
                        </button>
                        <span x-show="copiedReg" x-cloak class="text-[10px] font-sans font-bold text-emerald-700">Tersalin</span>
                    </div>

                    {{-- Nomor HP Orang Tua (Tautan WhatsApp) --}}
                    @if($hpOrtu && $waNumber)
                        <a href="https://wa.me/{{ $waNumber }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600"
                           title="Hubungi Orang Tua Siswa via WhatsApp">
                            <i class="fab fa-whatsapp text-emerald-600 text-xs" aria-hidden="true"></i>
                            <span>{{ $hpOrtu }}</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Tombol Aksi Header: Tombol Sekunder (Bukan Hitam) Cetak Kuitansi Pembayaran --}}
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                @if($item->totalTerbayar > 0)
                    <a href="{{ route('admin.daftar_ulang.kuitansi', $item->id) }}"
                       target="_blank"
                       class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 hover:text-slate-900 font-semibold rounded-xl text-sm transition-colors shadow-xs inline-flex items-center gap-2 min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                       title="Cetak Tanda Terima Kuitansi Pembayaran Sah">
                        <i class="fas fa-print text-xs text-slate-500" aria-hidden="true"></i>
                        <span>Cetak Kuitansi Pembayaran</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    @include('layout.alert')

    {{-- 2. Tiga Kartu Ringkasan (Grid 3 Kolom >= 1100px; 1 Kolom < 700px) --}}
    <div class="du-summary-grid items-stretch">
        {{-- Kartu Ringkasan 1: Status Keuangan --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between gap-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Status Keuangan</span>
                <div>
                    @if($item->statusKeuangan === 'Lunas')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <i class="fas fa-check-circle text-[10px]" aria-hidden="true"></i> Lunas
                        </span>
                    @elseif($item->statusKeuangan === 'Cicilan Awal Terpenuhi')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200">
                            <i class="fas fa-check-circle text-[10px]" aria-hidden="true"></i> Cicilan Awal Terpenuhi
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                            <i class="fas fa-clock text-[10px]" aria-hidden="true"></i> Belum Bayar
                        </span>
                    @endif
                </div>
            </div>

            <div class="my-2">
                <div class="text-lg sm:text-[20px] font-bold text-slate-900 tabular-nums whitespace-nowrap flex items-baseline gap-1.5">
                    <span>Rp {{ number_format($item->totalTerbayar, 0, ',', '.') }}</span>
                    <span class="text-xs sm:text-sm font-normal text-slate-500 whitespace-nowrap">/ Rp {{ number_format($item->totalBiaya, 0, ',', '.') }}</span>
                </div>
                {{-- Bilah Progres Tipis --}}
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2.5 overflow-hidden">
                    <div class="bg-emerald-600 h-1.5 rounded-full transition-all duration-300" style="width: {{ $item->persenTerbayar }}%"></div>
                </div>
            </div>

            <div class="text-xs sm:text-sm text-slate-600 pt-2 border-t border-slate-100 flex items-center justify-between">
                <span>Kewajiban Sisa</span>
                <span class="font-semibold tabular-nums {{ $item->sisaBiaya > 0 ? 'text-amber-800' : 'text-slate-500' }}">
                    Rp {{ number_format($item->sisaBiaya, 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Kartu Ringkasan 2: Berkas Fisik Sekolah --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between gap-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Berkas Fisik Sekolah</span>
                <div>
                    @if($item->statusBerkas === 'Diterima')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <i class="fas fa-check-circle text-[10px]" aria-hidden="true"></i> Lengkap
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            <i class="fas fa-clock text-[10px]" aria-hidden="true"></i> Menunggu
                        </span>
                    @endif
                </div>
            </div>

            <div class="my-2">
                <span class="text-lg sm:text-[20px] font-bold text-slate-900 block leading-tight">
                    {{ $item->statusBerkas === 'Diterima' ? 'Diterima' : 'Menunggu Berkas' }}
                </span>
            </div>

            <div class="text-xs sm:text-sm text-slate-600 pt-2 border-t border-slate-100 leading-snug" title="{{ $item->statusBerkas === 'Diterima' ? ($item->jenisBerkas ? 'Dokumen ' . $item->jenisBerkas : 'Dokumen fisik') . ($item->daftarUlang?->tgl_berkas_diterima ? ' • ' . format_school_time($item->daftarUlang->tgl_berkas_diterima, 'j M Y, H.i') : '') : 'Belum diserahkan ke sekolah' }}">
                @if($item->statusBerkas === 'Diterima')
                    <span>{{ $item->jenisBerkas ? 'Dokumen ' . $item->jenisBerkas : 'Dokumen fisik' }}</span>
                    @if($item->daftarUlang?->tgl_berkas_diterima)
                        <span class="text-slate-400 mx-1">•</span>
                        <span>{{ format_school_time($item->daftarUlang->tgl_berkas_diterima, 'j M Y, H.i') }}</span>
                    @endif
                @else
                    <span>Belum diserahkan ke sekolah</span>
                @endif
            </div>
        </div>

        {{-- Kartu Ringkasan 3: Status Daftar Ulang --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between gap-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Status Daftar Ulang</span>
                <div>
                    @if($item->statusDaftarUlang === 'Selesai')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <i class="fas fa-check-double text-[10px]" aria-hidden="true"></i> Terverifikasi
                        </span>
                    @elseif($item->statusDaftarUlang === 'Batal')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                            <i class="fas fa-times-circle text-[10px]" aria-hidden="true"></i> Batal
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            <i class="fas fa-hourglass-half text-[10px]" aria-hidden="true"></i> Batal
                        </span>
                    @endif
                </div>
            </div>

            <div class="my-2">
                <span class="text-lg sm:text-[20px] font-bold text-slate-900 block leading-tight">
                    {{ $item->statusDaftarUlang }}
                </span>
            </div>

            <div class="text-xs sm:text-sm text-slate-600 pt-2 border-t border-slate-100 leading-snug" title="{{ $item->statusDaftarUlang === 'Selesai' ? 'Cicilan pertama terpenuhi dan berkas diterima.' : ($item->statusDaftarUlang === 'Batal' ? 'Pendaftaran ulang dibatalkan.' : ($item->totalTerbayar < ($item->minCicilan1 ?? 500000) ? 'Menunggu cicilan pertama Rp ' . number_format($item->minCicilan1 ?? 500000, 0, ',', '.') : 'Menunggu berkas fisik diserahkan ke sekolah.')) }}">
                @if($item->statusDaftarUlang === 'Selesai')
                    <span>Cicilan pertama terpenuhi dan berkas diterima.</span>
                @elseif($item->statusDaftarUlang === 'Batal')
                    <span>Pendaftaran ulang dibatalkan.</span>
                @elseif($item->totalTerbayar < ($item->minCicilan1 ?? 500000))
                    <span>Menunggu cicilan pertama Rp {{ number_format($item->minCicilan1 ?? 500000, 0, ',', '.') }}.</span>
                @else
                    <span>Menunggu berkas fisik diserahkan ke sekolah.</span>
                @endif
            </div>
        </div>
    </div>

    {{-- 3. Wadah Grid Bersama (CSS Grid Berpasangan):
         >= 1000px: Grid 2 Kolom (3fr 2fr) dengan align-items: stretch.
         Baris 1 = Rincian Tagihan | Penerimaan Berkas Fisik (keduanya otomatis setinggi sama)
         Baris 2 = Riwayat Setoran | Pelunasan Tahap 2 (keduanya otomatis setinggi sama)
         < 1000px: 1 Kolom berurutan: Tagihan -> Riwayat -> Berkas -> Tahap 2.
    --}}
    <div class="du-grid-paired">

        {{-- ==================== ITEM 1: RINCIAN TAGIHAN PER KOMPONEN (Baris 1, Kolom 1) ==================== --}}
        <div class="du-card du-card-tagihan bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden flex flex-col h-full">
            {{-- Header Kartu Seragam (min-height: 84px, judul 20px, subjudul 14px) --}}
            <div class="px-6 py-4 min-h-[84px] border-b border-slate-100 bg-slate-50/50 flex flex-col justify-center">
                <h3 class="text-xl font-bold text-slate-900 leading-snug">Rincian Tagihan per Komponen</h3>
                <p class="text-sm text-slate-500 mt-0.5">Setoran dialokasikan: Buku, Seragam, lalu Pembangunan.</p>
            </div>

            {{-- Isi Kartu (flex: 1, tabel mengisi penuh, baris total menempel di dasar) --}}
            <div class="p-6 flex-1 flex flex-col justify-between">
                {{-- Tabel Desktop (sm ke atas) --}}
                <div class="overflow-x-auto hidden sm:block">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                <th scope="col" class="pb-3 px-3 first:pl-0">Komponen</th>
                                <th scope="col" class="pb-3 px-3 text-right whitespace-nowrap min-w-[100px]">Tagihan</th>
                                <th scope="col" class="pb-3 px-3 text-right whitespace-nowrap min-w-[100px]">Terbayar</th>
                                <th scope="col" class="pb-3 px-3 text-right whitespace-nowrap min-w-[100px]">Sisa</th>
                                <th scope="col" class="pb-3 px-3 last:pr-0 text-center whitespace-nowrap">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($item->components as $comp)
                            <tr>
                                <td class="py-3.5 px-3 first:pl-0 font-medium text-slate-800 text-sm">
                                    {{ $comp['nama'] }}
                                </td>
                                <td class="py-3.5 px-3 text-right text-slate-800 font-semibold tabular-nums text-sm whitespace-nowrap">
                                    Rp {{ number_format($comp['total'], 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 text-right tabular-nums text-sm whitespace-nowrap {{ $comp['terbayar'] > 0 ? 'text-emerald-700 font-bold' : 'text-slate-500 font-medium' }}">
                                    Rp {{ number_format($comp['terbayar'], 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 text-right tabular-nums text-sm whitespace-nowrap {{ $comp['sisa'] > 0 ? 'text-amber-800 font-bold' : 'text-slate-500 font-medium' }}">
                                    Rp {{ number_format($comp['sisa'], 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 last:pr-0 text-center whitespace-nowrap">
                                    @if($comp['sisa'] <= 0)
                                        <span class="inline-flex items-center gap-1.5 text-sm font-bold text-emerald-700">
                                            <i class="fas fa-check text-xs" aria-hidden="true"></i> Lunas
                                        </span>
                                    @elseif($comp['terbayar'] > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                            Sebagian
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            Belum Bayar
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200 bg-slate-50/50 font-bold text-sm">
                            <tr>
                                <td class="py-3.5 px-3 first:pl-0 text-slate-900 font-bold">Total Uang Pangkal</td>
                                <td class="py-3.5 px-3 text-right text-slate-900 font-bold tabular-nums whitespace-nowrap">
                                    Rp {{ number_format($item->totalBiaya, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 text-right tabular-nums whitespace-nowrap {{ $item->totalTerbayar > 0 ? 'text-emerald-700 font-bold' : 'text-slate-500 font-medium' }}">
                                    Rp {{ number_format($item->totalTerbayar, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 text-right tabular-nums whitespace-nowrap {{ $item->sisaBiaya > 0 ? 'text-amber-800 font-bold' : 'text-slate-500 font-medium' }}">
                                    Rp {{ number_format($item->sisaBiaya, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 last:pr-0 text-center whitespace-nowrap">
                                    @if($item->sisaBiaya <= 0)
                                        <span class="inline-flex items-center gap-1.5 text-sm font-bold text-emerald-700">
                                            <i class="fas fa-check text-xs" aria-hidden="true"></i> Lunas
                                        </span>
                                    @else
                                        <span class="text-sm text-amber-800 font-bold tabular-nums">{{ $item->persenTerbayar }}%</span>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Tampilan Mobile (< 600px): Kartu Komponen Mandiri (Bebas Scroll Horizontal) --}}
                <div class="sm:hidden space-y-3">
                    @foreach($item->components as $comp)
                    <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/80 space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <span class="font-semibold text-slate-900 text-xs">{{ $comp['nama'] }}</span>
                            @if($comp['sisa'] <= 0)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 shrink-0">
                                    <i class="fas fa-check text-[9px]" aria-hidden="true"></i> Lunas
                                </span>
                            @elseif($comp['terbayar'] > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-800 border border-blue-200 shrink-0">
                                    Sebagian
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200 shrink-0">
                                    Belum
                                </span>
                            @endif
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-xs pt-1 border-t border-slate-200">
                            <div>
                                <span class="text-[10px] text-slate-500 block uppercase">Tagihan</span>
                                <span class="font-semibold text-slate-800 tabular-nums whitespace-nowrap">Rp {{ number_format($comp['total'], 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 block uppercase">Terbayar</span>
                                <span class="font-bold tabular-nums whitespace-nowrap {{ $comp['terbayar'] > 0 ? 'text-emerald-700' : 'text-slate-400' }}">Rp {{ number_format($comp['terbayar'], 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 block uppercase">Sisa</span>
                                <span class="font-semibold tabular-nums whitespace-nowrap {{ $comp['sisa'] > 0 ? 'text-amber-800' : 'text-slate-400' }}">Rp {{ number_format($comp['sisa'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    {{-- Total Mobile --}}
                    <div class="bg-emerald-50/70 p-3.5 rounded-xl border border-emerald-200 flex items-center justify-between text-xs font-bold text-slate-900">
                        <span>Total Uang Pangkal</span>
                        <div class="text-right">
                            <span class="block text-emerald-800 tabular-nums text-sm">Rp {{ number_format($item->totalTerbayar, 0, ',', '.') }}</span>
                            <span class="text-[10px] font-normal text-slate-500">dari Rp {{ number_format($item->totalBiaya, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== ITEM 2: RIWAYAT SETORAN PEMBAYARAN (Baris 2, Kolom 1) ==================== --}}
        <div class="du-card du-card-riwayat bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden flex flex-col h-full"
             x-data="{ expanded: false }">
            {{-- Header Kartu Seragam (min-height: 84px, judul 20px, subjudul 14px) --}}
            <div class="px-6 py-4 min-h-[84px] border-b border-slate-100 bg-slate-50/50 flex items-center justify-between gap-4">
                <div class="flex flex-col justify-center">
                    <h3 class="text-xl font-bold text-slate-900 leading-snug">Riwayat Setoran</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Pembayaran lewat gateway dan yang dicatat admin.</p>
                </div>

                @if($item->sisaBiaya > 0)
                    <button type="button"
                            @click="modalBayar = true"
                            class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 hover:text-slate-900 text-sm font-semibold rounded-xl shadow-xs transition-colors inline-flex items-center gap-2 min-h-[44px] shrink-0 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        <i class="fas fa-plus text-xs text-slate-500" aria-hidden="true"></i>
                        <span>Catat Tunai</span>
                    </button>
                @endif
            </div>

            {{-- Isi Kartu (flex: 1, daftar setoran di atas, keadaan kosong rata tengah) --}}
            @php
                $totalSetoran = $item->riwayatPembayaran->count();
                $setoranList = $item->riwayatPembayaran;
            @endphp

            @if($totalSetoran > 0)
                <div class="flex-1 flex flex-col divide-y divide-slate-100">
                    @foreach($setoranList as $index => $bayar)
                    @php
                        $isTunai = strcasecmp($bayar->metode_pembayaran ?? '', 'tunai') === 0;
                        $waktuTampil = $bayar->waktu_lunas ?? $bayar->tgl_bayar_du;
                        $isBatal = in_array($bayar->status_du, ['Kadaluarsa', 'Gagal', 'Ditolak', 'Batal']);
                        $isValid = in_array($bayar->status_du, ['Lunas', 'Diterima']);

                        // Ekstraksi Bank Virtual Account jika tersedia di va_number (misal: "bni - 9883086816528476")
                        $bankName = null;
                        if (!empty($bayar->va_number)) {
                            $parts = explode('-', $bayar->va_number);
                            $candidate = trim($parts[0] ?? '');
                            if (!empty($candidate)) {
                                $bankName = strtoupper($candidate);
                            }
                        }

                        if ($isTunai) {
                            $metodeLabel = 'Tunai';
                        } elseif ($bankName && !in_array($bankName, ['MIDTRANS', 'GATEWAY'])) {
                            $metodeLabel = 'Gateway - Virtual Account ' . $bankName;
                        } else {
                            $metodeLabel = 'Gateway';
                        }

                        $pencatat = $bayar->dicatat_oleh ?: ($isTunai ? 'Kasir TU' : 'Otomatis (Midtrans)');
                    @endphp
                    <div x-show="expanded || {{ $index }} < 5"
                         class="p-4 sm:p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 hover:bg-slate-50/50 transition-colors {{ $isBatal ? 'opacity-70' : '' }}">
                        <div class="space-y-2 flex-1 min-w-0">
                            {{-- Baris 1: Status & Metode --}}
                            <div class="flex flex-wrap items-center gap-2">
                                @if($isValid)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <i class="fas fa-check-circle text-[10px]" aria-hidden="true"></i> Valid
                                    </span>
                                @elseif($isBatal)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                        <i class="fas fa-times-circle text-[10px]" aria-hidden="true"></i> Dibatalkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <i class="fas fa-clock text-[10px]" aria-hidden="true"></i> Menunggu
                                    </span>
                                @endif

                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isTunai ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-blue-50 text-blue-900 border border-blue-200' }}">
                                    {{ $metodeLabel }}
                                </span>
                            </div>

                            {{-- Baris 2: Referensi (Order ID / No. Kuitansi) dengan Tombol Salin --}}
                            <div>
                                @if($bayar->order_id_du)
                                    <div class="inline-flex items-center gap-1.5 text-xs font-mono text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200"
                                         x-data="{ copiedOrder: false }">
                                        <span title="{{ $bayar->order_id_du }}">{{ Str::limit($bayar->order_id_du, 24, '…') }}</span>
                                        <button type="button"
                                                @click="navigator.clipboard.writeText('{{ $bayar->order_id_du }}'); copiedOrder = true; setTimeout(() => copiedOrder = false, 2000)"
                                                class="text-slate-400 hover:text-slate-700 p-0.5 cursor-pointer rounded focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-emerald-600"
                                                :title="copiedOrder ? 'Tersalin!' : 'Salin Order ID'"
                                                aria-label="Salin Order ID">
                                            <i :class="copiedOrder ? 'fas fa-check text-emerald-600' : 'fas fa-copy'" class="text-[11px]" aria-hidden="true"></i>
                                        </button>
                                        <span x-show="copiedOrder" x-cloak class="text-[10px] font-sans font-bold text-emerald-700">Tersalin</span>
                                    </div>
                                @elseif($bayar->no_kuitansi)
                                    <span class="font-mono text-xs text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                                        No. Kuitansi: {{ $bayar->no_kuitansi }}
                                    </span>
                                @endif
                            </div>

                            {{-- Baris 3: Meta Info (Waktu, Separator Titik Tengah, Dicatat Oleh) Bebas Tabrakan Teks --}}
                            <div class="text-sm text-slate-600 flex flex-wrap items-center gap-y-1">
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="w-4 h-4 flex items-center justify-center shrink-0 text-slate-400">
                                        <i class="fas fa-calendar-day text-xs" aria-hidden="true"></i>
                                    </span>
                                    <span>{{ format_school_time($waktuTampil, 'j M Y, H.i') }}</span>
                                </div>
                                <span class="mx-2 text-slate-300 select-none">•</span>
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="w-4 h-4 flex items-center justify-center shrink-0 text-slate-400">
                                        <i class="fas fa-user-edit text-xs" aria-hidden="true"></i>
                                    </span>
                                    <span>Dicatat: <strong class="text-slate-800 font-semibold">{{ $pencatat }}</strong></span>
                                </div>
                            </div>

                            @if($bayar->keterangan)
                                <p class="text-xs text-slate-600 italic bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                                    "{{ $bayar->keterangan }}"
                                </p>
                            @endif

                            {{-- TODO: Tampilkan alokasi per setoran (misal: Buku X - Seragam Y - Pembangunan Z) jika relasi alokasi per transaksi setoran sudah disimpan di database --}}
                        </div>

                        {{-- Kolom Kanan: Label Jumlah, Nominal 18px Bold, dan Tombol Kuitansi Sekunder 44px --}}
                        <div class="flex flex-col sm:items-end justify-center shrink-0 gap-1.5 mt-2 sm:mt-0 w-full sm:w-auto">
                            <div class="text-left sm:text-right">
                                <span class="text-xs font-bold text-slate-500 block uppercase tracking-wider">Jumlah</span>
                                <span class="text-lg font-bold tabular-nums block whitespace-nowrap {{ $isBatal ? 'line-through text-slate-400' : 'text-slate-900' }}">
                                    Rp {{ number_format($bayar->jml_bayar_du, 0, ',', '.') }}
                                </span>
                            </div>

                            @if($isValid)
                                <a href="{{ route('admin.daftar_ulang.kuitansi', ['id' => $item->id, 'bayarId' => $bayar->id_bayar_daftar_ulang]) }}"
                                   target="_blank"
                                   class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 hover:text-slate-900 text-xs font-semibold rounded-xl shadow-xs inline-flex items-center justify-center gap-2 transition-colors min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 w-full sm:w-auto"
                                   title="Cetak Kuitansi Transaksi Ini">
                                    <i class="fas fa-print text-xs text-slate-500" aria-hidden="true"></i>
                                    <span>Kuitansi</span>
                                </a>
                            @endif

                            {{-- TODO: Tambahkan tombol Batalkan Setoran jika route POST /admin/verifikasi-daftar-ulang/{id}/setoran/{bayarId}/batalkan sudah diimplementasikan di controller --}}
                        </div>
                    </div>
                    @endforeach

                    {{-- Tombol Perluas jika lebih dari 5 transaksi --}}
                    @if($totalSetoran > 5)
                        <div class="p-3 text-center border-t border-slate-100 bg-slate-50/50 mt-auto">
                            <button type="button"
                                    @click="expanded = !expanded"
                                    class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 transition-colors inline-flex items-center gap-1.5 py-1.5 px-3.5 rounded-lg hover:bg-emerald-50 cursor-pointer min-h-[36px]">
                                <span x-text="expanded ? 'Tampilkan lebih sedikit' : 'Lihat semua ({{ $totalSetoran }})'"></span>
                                <i class="fas" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'" aria-hidden="true"></i>
                            </button>
                        </div>
                    @endif
                </div>
            @else
                <div class="p-8 text-center text-slate-400 text-sm flex-1 flex flex-col items-center justify-center">
                    <i class="fas fa-receipt text-3xl mb-2 opacity-40" aria-hidden="true"></i>
                    <p>Belum ada riwayat transaksi pembayaran.</p>
                </div>
            @endif
        </div>

        {{-- ==================== ITEM 3: PENERIMAAN BERKAS FISIK (Baris 1, Kolom 2) ==================== --}}
        <div class="du-card du-card-berkas bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden flex flex-col h-full">
            {{-- Header Kartu Seragam (min-height: 84px, judul 20px, subjudul 14px, tombol Ubah rata tengah vertikal) --}}
            <div class="px-6 py-4 min-h-[84px] border-b border-slate-100 bg-slate-50/50 flex items-center justify-between gap-4">
                <div class="flex flex-col justify-center">
                    <h3 class="text-xl font-bold text-slate-900 leading-snug">Penerimaan Berkas Fisik</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Verifikasi dokumen asli / fotokopi ke sekolah</p>
                </div>
                <button type="button"
                        @click="modalBerkas = true"
                        class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 hover:text-slate-900 text-sm font-semibold rounded-xl transition-colors inline-flex items-center gap-2 min-h-[44px] shrink-0 cursor-pointer shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                    <i class="fas fa-pen text-xs text-slate-500" aria-hidden="true"></i>
                    <span>Ubah</span>
                </button>
            </div>

            {{-- Isi Kartu (flex: 1, grid 2x2 berukuran seragam, kotak Catatan flex: 1 mengisi sisa tinggi) --}}
            <div class="p-6 flex-1 flex flex-col justify-between gap-4">
                {{-- Grid 2x2 Info dengan Ukuran Seragam: Label 12px uppercase, Nilai 14-15px semibold warna netral gelap --}}
                <div class="grid grid-cols-2 gap-3.5">
                    {{-- Kotak 1: Status Berkas --}}
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex flex-col justify-between min-h-[82px]">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Status Berkas</span>
                        <span class="text-[14px] sm:text-[15px] font-semibold text-slate-800 block mt-1">
                            {{ $item->statusBerkas === 'Diterima' ? 'Diterima' : 'Menunggu Berkas' }}
                        </span>
                    </div>

                    {{-- Kotak 2: Jenis Penyerahan --}}
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex flex-col justify-between min-h-[82px]">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Jenis Penyerahan</span>
                        <span class="text-[14px] sm:text-[15px] font-semibold text-slate-800 block mt-1">
                            {{ $item->jenisBerkas ? 'Dokumen ' . $item->jenisBerkas : 'Belum ditentukan' }}
                        </span>
                    </div>

                    {{-- Kotak 3: Waktu Diterima --}}
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex flex-col justify-between min-h-[82px]">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Waktu Diterima</span>
                        <span class="text-[14px] sm:text-[15px] font-semibold text-slate-800 block mt-1 whitespace-nowrap">
                            {{ $item->daftarUlang?->tgl_berkas_diterima ? format_school_time($item->daftarUlang->tgl_berkas_diterima, 'j M Y, H.i') : '-' }}
                        </span>
                    </div>

                    {{-- Kotak 4: Penerima Berkas --}}
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex flex-col justify-between min-h-[82px]">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Penerima Berkas</span>
                        <span class="text-[14px] sm:text-[15px] font-semibold text-slate-800 block mt-1">
                            {{ $item->daftarUlang?->penerima_berkas ?: '-' }}
                        </span>
                    </div>
                </div>

                {{-- Kotak Catatan: flex: 1 mengisi sisa tinggi dengan teks di atas --}}
                <div class="flex-1 flex flex-col min-h-[100px]">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Catatan</span>
                    <div class="flex-1 bg-slate-50 p-4 rounded-xl border border-slate-100 text-sm text-slate-700 leading-relaxed flex items-start">
                        <p>{{ $item->daftarUlang?->catatan_berkas ?: 'Tidak ada catatan untuk berkas ini.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== ITEM 4: PELUNASAN TAHAP 2 (Baris 2, Kolom 2) ==================== --}}
        <div class="du-card du-card-tahap2 bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden flex flex-col h-full">
            {{-- Header Kartu Seragam (min-height: 84px, judul 20px, subjudul 14px) --}}
            <div class="px-6 py-4 min-h-[84px] border-b border-slate-100 bg-slate-50/50 flex flex-col justify-center">
                <h3 class="text-xl font-bold text-slate-900 leading-snug">Pelunasan Tahap 2</h3>
                <p class="text-sm text-slate-500 mt-0.5">Sisa biaya pembangunan &amp; sarana sekolah</p>
            </div>

            {{-- Isi Kartu (flex: 1, kotak status flex: 1 rata tengah vertikal) --}}
            <div class="p-6 flex-1 flex flex-col justify-between">
                @if($item->sisaBiaya <= 0)
                    {{-- Keadaan Lunas Sekaligus: Bayar Lunas tidak punya tahap 2, rata tengah vertikal --}}
                    <div class="flex-1 flex items-center justify-center">
                        <div class="w-full bg-emerald-50/80 border border-emerald-200 rounded-xl p-5 text-emerald-900 flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <i class="fas fa-check-circle text-lg" aria-hidden="true"></i>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-base font-bold text-emerald-900">Dibayar Lunas Sekaligus</h4>
                                <p class="text-sm text-emerald-800 leading-relaxed">
                                    Seluruh kewajiban uang pangkal telah lunas pada setoran awal. Tidak ada tagihan atau kewajiban tahap 2.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Keadaan Masih Ada Tagihan Tahap 2 --}}
                    <div class="space-y-4 flex-1 flex flex-col justify-between">
                        <div class="grid grid-cols-2 gap-3.5">
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex flex-col justify-between min-h-[82px]">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Sisa Tagihan Tahap 2</span>
                                <span class="text-base font-bold text-slate-900 block mt-1 tabular-nums">
                                    Rp {{ number_format($item->sisaBiaya, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex flex-col justify-between min-h-[82px]">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Batas Jatuh Tempo</span>
                                <span class="text-[14px] sm:text-[15px] font-semibold text-slate-800 block mt-1">
                                    {{ $item->tglJatuhTempo ? Carbon\Carbon::parse($item->tglJatuhTempo)->isoFormat('D MMMM Y') : 'Belum Ditentukan' }}
                                </span>
                            </div>

                            {{-- Status Pelunasan dengan Tata Letak Vertikal Bersih (Bebas Tabrakan Teks) --}}
                            <div class="col-span-2 bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status Pelunasan</span>
                                    <div>
                                        @if($item->statusTahap2 === 'Terlambat')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                                <i class="fas fa-exclamation-circle text-[10px]" aria-hidden="true"></i> Melewati Jatuh Tempo
                                            </span>
                                        @elseif($item->statusTahap2 === 'Jatuh Tempo')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                <i class="fas fa-clock text-[10px]" aria-hidden="true"></i> Jatuh Tempo Hari Ini
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                                <i class="fas fa-calendar-alt text-[10px]" aria-hidden="true"></i> Belum Jatuh Tempo
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-sm text-slate-500">Kewajiban pelunasan biaya sarana &amp; pembangunan tahap 2.</p>
                            </div>
                        </div>

                        {{-- Tombol Aksi Catat Pelunasan Tunai --}}
                        <div class="pt-2 mt-auto">
                            <button type="button"
                                    @click="modalBayar = true"
                                    class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-xs inline-flex items-center justify-center gap-2 min-h-[44px] cursor-pointer">
                                <i class="fas fa-money-bill-wave text-xs" aria-hidden="true"></i>
                                <span>Catat Pelunasan Tunai</span>
                            </button>
                        </div>

                        {{-- TODO: Tambahkan tombol aksi Perpanjang Jatuh Tempo jika route POST /admin/verifikasi-daftar-ulang/{id}/perpanjang-jatuh-tempo sudah tersedia di backend --}}
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ========================================================
         MODAL 1: CATAT PEMBAYARAN TUNAI
         ======================================================== --}}
    <div x-show="modalBayar"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="modalBayar = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6 border border-slate-200"
                 @click.stop>
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-lg" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Catat Pembayaran Tunai</h3>
                            <p class="text-xs text-slate-500">Pencatatan setoran langsung di kasir sekolah</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="modalBayar = false"
                            class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center cursor-pointer">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>

                <form action="{{ route('admin.daftar_ulang.catat_pembayaran', $item->id) }}"
                      method="POST"
                      class="mt-4 space-y-4">
                    @csrf

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80 text-xs text-slate-700 space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Calon Siswa:</span>
                            <span class="font-bold text-slate-800">{{ $nama }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">No. Registrasi:</span>
                            <span class="font-mono font-semibold text-slate-700">{{ $noReg }}</span>
                        </div>
                        <div class="flex justify-between pt-1 border-t border-slate-200">
                            <span class="text-slate-500">Sisa Tagihan:</span>
                            <span class="font-bold text-amber-700 text-sm">
                                Rp {{ number_format($item->sisaBiaya, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="jml_bayar_du_show" class="block text-xs font-semibold text-slate-700">
                                Nominal Pembayaran (Rp) <span class="text-red-500">*</span>
                            </label>
                            <button type="button"
                                    @click="$refs.inputNominalShow.value = {{ $item->sisaBiaya }}"
                                    class="text-[11px] font-semibold text-blue-600 hover:text-blue-800 cursor-pointer">
                                Bayar Penuh Sisa
                            </button>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm font-bold">
                                Rp
                            </div>
                            <input type="number"
                                   name="jml_bayar_du"
                                   id="jml_bayar_du_show"
                                   x-ref="inputNominalShow"
                                   required
                                   min="1000"
                                   max="{{ max(1000, $item->sisaBiaya) }}"
                                   value="{{ $item->sisaBiaya }}"
                                   class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm font-bold text-slate-800 min-h-[44px]">
                        </div>
                    </div>

                    <div>
                        <label for="tgl_bayar_du_show" class="block text-xs font-semibold text-slate-700 mb-1">
                            Tanggal Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local"
                               name="tgl_bayar_du"
                               id="tgl_bayar_du_show"
                               required
                               value="{{ now()->format('Y-m-d\TH:i') }}"
                               class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm min-h-[44px]">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="no_kuitansi_show" class="block text-xs font-semibold text-slate-700 mb-1">
                                No. Kuitansi (Opsional)
                            </label>
                            <input type="text"
                                   name="no_kuitansi"
                                   id="no_kuitansi_show"
                                   placeholder="Otomatis jika kosong"
                                   class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm min-h-[44px]">
                        </div>
                        <div>
                            <label for="keterangan_show" class="block text-xs font-semibold text-slate-700 mb-1">
                                Keterangan Setoran
                            </label>
                            <input type="text"
                                   name="keterangan"
                                   id="keterangan_show"
                                   placeholder="Contoh: Setoran tunai di TU"
                                   class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm min-h-[44px]">
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button"
                                @click="modalBayar = false"
                                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl min-h-[44px] transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors min-h-[44px] inline-flex items-center gap-2 cursor-pointer">
                            <i class="fas fa-save text-xs" aria-hidden="true"></i>
                            <span>Simpan Pembayaran Tunai</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================================================
         MODAL 2: TERIMA / PERBARUI BERKAS FISIK
         ======================================================== --}}
    <div x-show="modalBerkas"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="modalBerkas = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 border border-slate-200"
                 @click.stop>
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center">
                            <i class="fas fa-folder-open text-lg" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Penerimaan Berkas Fisik</h3>
                            <p class="text-xs text-slate-500">Verifikasi berkas persyaratan calon siswa</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="modalBerkas = false"
                            class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center cursor-pointer">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>

                <form action="{{ route('admin.daftar_ulang.terima_berkas', $item->id) }}"
                      method="POST"
                      class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <label for="status_berkas_show" class="block text-xs font-semibold text-slate-700 mb-1">
                            Status Berkas Fisik <span class="text-red-500">*</span>
                        </label>
                        <select name="status_berkas"
                                id="status_berkas_show"
                                required
                                class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 text-sm font-semibold min-h-[44px]">
                            <option value="Menunggu Berkas" {{ $item->statusBerkas === 'Menunggu Berkas' ? 'selected' : '' }}>⏳ Menunggu Berkas Diserahkan</option>
                            <option value="Diterima" {{ $item->statusBerkas === 'Diterima' ? 'selected' : '' }}>✅ Berkas Diterima Lengkap</option>
                            <option value="Ditolak" {{ $item->statusBerkas === 'Ditolak' ? 'selected' : '' }}>❌ Berkas Ditolak / Tidak Sesuai</option>
                        </select>
                    </div>

                    <div>
                        <label for="jenis_berkas_show" class="block text-xs font-semibold text-slate-700 mb-1">
                            Jenis Penyerahan Dokumen
                        </label>
                        <select name="jenis_berkas"
                                id="jenis_berkas_show"
                                class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 text-sm min-h-[44px]">
                            <option value="Asli" {{ $item->jenisBerkas === 'Asli' ? 'selected' : '' }}>Dokumen Asli</option>
                            <option value="Fotokopi" {{ $item->jenisBerkas === 'Fotokopi' ? 'selected' : '' }}>Fotokopi Dilegalisir</option>
                            <option value="Asli &amp; Fotokopi" {{ $item->jenisBerkas === 'Asli & Fotokopi' ? 'selected' : '' }}>Asli &amp; Fotokopi Lengkap</option>
                        </select>
                    </div>

                    <div>
                        <label for="catatan_berkas_show" class="block text-xs font-semibold text-slate-700 mb-1">
                            Catatan Panitia (Opsional)
                        </label>
                        <textarea name="catatan_berkas"
                                  id="catatan_berkas_show"
                                  rows="2"
                                  placeholder="Contoh: Akta dan Kartu Keluarga asli telah diperiksa langsung."
                                  class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 text-sm">{{ $item->daftarUlang?->catatan_berkas }}</textarea>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button"
                                @click="modalBerkas = false"
                                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl min-h-[44px] transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors min-h-[44px] inline-flex items-center gap-2 cursor-pointer">
                            <i class="fas fa-check text-xs" aria-hidden="true"></i>
                            <span>Simpan Status Berkas</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
