@extends('layout.tampilancasis')

@section('page-title', 'Profil Saya')
@section('title', 'Profil Saya')

@section('content')
@php
    $namaAkun = $user->nama ?? $user->name ?? 'Orang Tua / Wali';
    $initialAkun = strtoupper(substr(trim($namaAkun), 0, 1) ?: 'O');

    $isArchived = !empty($isArchived);
    $casis = $user->calonSiswa ?? $user->casis ?? $casis ?? $archivedCasis;
    $namaSiswa = $casis?->nama_calon_siswa ?? $casis?->nama;
    $noReg = $casis?->no_registrasi;

    // Normalisasi Status Pendaftaran (tampilan konsisten dengan halaman Pendaftaran)
    $rawStatus = $casis?->status_pendaftaran ?? 'Menunggu';
    $statusDisplayMap = [
        'Diterima' => [
            'label' => 'Data Terverifikasi',
            'badgeClass' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'icon' => 'fa-check-circle text-emerald-700',
        ],
        'Ditolak' => [
            'label' => 'Perlu Perbaikan',
            'badgeClass' => 'bg-rose-50 text-rose-800 border-rose-200',
            'icon' => 'fa-exclamation-circle text-rose-700',
        ],
        'Menunggu' => [
            'label' => 'Menunggu Verifikasi',
            'badgeClass' => 'bg-amber-50 text-amber-800 border-amber-200',
            'icon' => 'fa-clock text-amber-700',
        ],
    ];

    if ($isArchived) {
        $statusInfo = [
            'label' => 'Data Diarsipkan',
            'badgeClass' => 'bg-amber-50 text-amber-800 border-amber-300',
            'icon' => 'fa-archive text-amber-700',
        ];
    } else {
        $statusInfo = $statusDisplayMap[$rawStatus] ?? $statusDisplayMap['Menunggu'];
    }

    $hasEditErrors = $errors->has('nama') || $errors->has('email') || $errors->has('no_hp') || $errors->has('password_saat_ini');
    $hasPasswordErrors = $errors->has('password_lama') || $errors->has('password_baru') || $errors->has('password_baru_confirmation');

    $nomorHpTampil = $user->no_hp ?? $casis?->no_hp_orang_tua ?? null;

    // Helper format nomor HP untuk keterbacaan (contoh: 0812-3187-9121)
    $formatHp = function($number) {
        $digits = preg_replace('/[^0-9]/', '', (string)$number);
        if (empty($digits)) return '';
        $len = strlen($digits);
        if ($len <= 4) return $digits;
        if ($len <= 8) return substr($digits, 0, 4) . '-' . substr($digits, 4);
        if ($len <= 12) {
            return substr($digits, 0, 4) . '-' . substr($digits, 4, 4) . '-' . substr($digits, 8);
        }
        return substr($digits, 0, 4) . '-' . substr($digits, 4, 4) . '-' . substr($digits, 8, 4) . '-' . substr($digits, 12);
    };
    $nomorHpFormatted = !empty($nomorHpTampil) ? $formatHp($nomorHpTampil) : null;
@endphp

{{-- Scoped CSS untuk Halaman Profil Saya & Modal Aksesibel --}}
<style>
    .profil-page,
    .profil-page h1, .profil-page h2, .profil-page h3,
    .profil-page h4, .profil-page p, .profil-page span,
    .profil-page dt, .profil-page dd, .profil-page a,
    .profil-page button, .profil-page input, .profil-page label {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
    }

    .profil-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        align-items: start;
    }
    @media (min-width: 900px) {
        .profil-grid {
            grid-template-columns: 380px 1fr;
            gap: 28px;
        }
    }

    .profil-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05);
        transform: none !important;
        transition: none !important;
        animation: none !important;
        overflow: hidden;
    }
    .profil-card:hover {
        transform: none !important;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05) !important;
    }

    .profil-row-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding-top: 14px;
        padding-bottom: 14px;
        min-height: 50px;
        border-bottom: 1px solid #f1f5f9;
        box-sizing: border-box;
    }
    .profil-row-item:last-child {
        border-bottom: none;
    }
    @media (min-width: 600px) {
        .profil-row-item {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .profil-row-label {
            width: 160px;
            min-width: 160px;
            flex-shrink: 0;
            text-align: left;
        }
        .profil-row-val {
            text-align: right;
            flex: 1;
        }
    }

    .profil-row-label {
        font-size: 0.875rem; /* 14px */
        font-weight: 500;
        color: #475569; /* slate-600 */
    }
    .profil-row-val {
        font-size: 1rem; /* 16px */
        font-weight: 600;
        color: #0f172a; /* slate-900 */
        word-break: break-word;
    }

    /* =========================================================
       MODAL STYLES (Aksesibel, 520px max-width, Bottom Sheet <600px)
       ========================================================= */
    .profil-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background-color: rgba(15, 23, 42, 0.65); /* slate-900 65% */
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        overflow-y: auto;
        box-sizing: border-box;
    }

    .profil-modal-container {
        position: relative;
        width: 100%;
        max-width: 520px;
        max-height: calc(100dvh - 32px);
        margin: auto;
        background-color: #ffffff;
        border-radius: 16px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        border: 1px solid #cbd5e1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .profil-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        background-color: #f8fafc;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-shrink: 0;
    }

    .profil-modal-body {
        padding: 24px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 20px; /* Jarak antar kolom 16-20px */
    }

    .profil-modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #f1f5f9;
        background-color: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        flex-shrink: 0;
    }

    .profil-input-field {
        width: 100%;
        height: 48px;
        min-height: 48px;
        font-size: 16px !important; /* 16px mencegah zoom otomatis di iOS */
        padding: 0 16px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        background-color: #ffffff;
        box-sizing: border-box;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .profil-input-field:focus {
        border-color: #059669; /* emerald-600 */
        box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2);
    }
    .profil-input-field.input-error {
        border-color: #e11d48; /* rose-600 */
    }
    .profil-input-field.input-error:focus {
        box-shadow: 0 0 0 2px rgba(225, 29, 72, 0.2);
    }

    .profil-btn-close {
        width: 44px;
        min-width: 44px;
        height: 44px;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        color: #334155; /* slate-700 (rasio kontras > 7:1) */
        background: transparent;
        border: none;
        cursor: pointer;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .profil-btn-close:hover {
        background-color: #e2e8f0;
        color: #0f172a;
    }
    .profil-btn-close:focus-visible {
        outline: 2px solid #059669;
        outline-offset: 2px;
    }

    .profil-btn-primary {
        height: 44px;
        min-height: 44px;
        padding: 0 20px;
        font-size: 14px;
        font-weight: 600;
        color: #ffffff;
        background-color: #059669; /* emerald-600 */
        border: 1px solid #059669;
        border-radius: 10px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }
    .profil-btn-primary:hover {
        background-color: #047857;
        border-color: #047857;
    }
    .profil-btn-primary:focus-visible {
        outline: 2px solid #059669;
        outline-offset: 2px;
    }

    .profil-btn-secondary {
        height: 44px;
        min-height: 44px;
        padding: 0 18px;
        font-size: 14px;
        font-weight: 600;
        color: #334155; /* slate-700 */
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }
    .profil-btn-secondary:hover {
        background-color: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8;
    }
    .profil-btn-secondary:focus-visible {
        outline: 2px solid #64748b;
        outline-offset: 2px;
    }

    /* Eye toggle inside password field */
    .profil-eye-toggle {
        width: 44px;
        min-width: 44px;
        height: 44px;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        right: 2px;
        top: 2px;
        color: #475569; /* slate-600 */
        background: transparent;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }
    .profil-eye-toggle:hover {
        color: #0f172a;
        background-color: #f1f5f9;
    }
    .profil-eye-toggle:focus-visible {
        outline: 2px solid #059669;
        outline-offset: 1px;
    }

    /* Layar < 600px: Modal Berubah Menjadi Bottom Sheet */
    @media (max-width: 599.98px) {
        .profil-modal-overlay {
            align-items: flex-end !important;
            padding: 0 !important;
        }
        .profil-modal-container {
            width: 100% !important;
            max-width: 100% !important;
            border-radius: 20px 20px 0 0 !important;
            max-height: calc(100dvh - 24px) !important;
            margin: 0 !important;
            border-bottom: none !important;
            padding-bottom: env(safe-area-inset-bottom, 16px);
        }
        .profil-modal-header {
            padding: 16px 20px !important;
        }
        .profil-modal-body {
            padding: 20px !important;
            gap: 16px !important;
        }
        .profil-modal-footer {
            padding: 16px 20px !important;
            flex-direction: column-reverse !important; /* Tombol Simpan (Utama) di atas, Batal di bawah */
            gap: 10px !important;
        }
        .profil-btn-primary,
        .profil-btn-secondary {
            width: 100% !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .profil-card,
        .profil-card *,
        .profil-modal-container,
        .profil-modal-container * {
            transition: none !important;
            animation: none !important;
            transform: none !important;
        }
    }
</style>

<div class="profil-page space-y-6 pb-20"
     x-data="{
        openModalEdit: {{ $hasEditErrors ? 'true' : 'false' }},
        openModalPassword: {{ $hasPasswordErrors ? 'true' : 'false' }},

        initialNama: '{{ addslashes($user->nama ?? '') }}',
        inputNama: '{{ addslashes(old('nama', $user->nama ?? '')) }}',

        initialEmail: '{{ strtolower(trim($user->email ?? '')) }}',
        editEmail: '{{ strtolower(trim(old('email', $user->email ?? ''))) }}',

        initialNoHp: '{{ addslashes($user->no_hp ?? $casis?->no_hp_orang_tua ?? '') }}',
        inputNoHp: '{{ addslashes(old('no_hp', $user->no_hp ?? $casis?->no_hp_orang_tua ?? '')) }}',

        inputPassLama: '',
        inputPassBaru: '',
        inputPassKonf: '',

        isEditDirty() {
            return (this.inputNama.trim() !== this.initialNama.trim()) ||
                   (this.editEmail.trim() !== this.initialEmail.trim()) ||
                   (this.inputNoHp.trim() !== this.initialNoHp.trim());
        },

        isPasswordDirty() {
            return (this.inputPassLama !== '') ||
                   (this.inputPassBaru !== '') ||
                   (this.inputPassKonf !== '');
        },

        openEdit() {
            this.openModalEdit = true;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => {
                document.getElementById('input_nama')?.focus();
            });
        },

        closeModalEdit() {
            if (this.isEditDirty()) {
                if (!confirm('Perubahan data akun belum disimpan. Yakin ingin menutup?')) {
                    return;
                }
            }
            this.openModalEdit = false;
            document.body.style.overflow = '';
            this.$refs.btnOpenEdit?.focus();
        },

        openPassword() {
            this.openModalPassword = true;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => {
                document.getElementById('input_password_lama')?.focus();
            });
        },

        closeModalPassword() {
            if (this.isPasswordDirty()) {
                if (!confirm('Perubahan kata sandi belum disimpan. Yakin ingin menutup?')) {
                    return;
                }
            }
            this.openModalPassword = false;
            document.body.style.overflow = '';
            this.$refs.btnOpenPassword?.focus();
        }
     }"
     @keydown.escape.window="if (openModalEdit) closeModalEdit(); if (openModalPassword) closeModalPassword();">

    {{-- Alert Sukses --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between gap-3 text-emerald-800 text-sm font-medium shadow-xs" role="status" aria-live="polite">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-600 text-base shrink-0" aria-hidden="true"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 p-1 rounded-md" aria-label="Tutup pemberitahuan">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl text-slate-800 font-bold font-heading">
            Profil Saya
        </h1>
        <p class="text-slate-500 text-sm mt-1">
            Kelola data akun Anda.
        </p>
    </div>

    {{-- Layout Dua Kolom: Identitas Akun (Kiri) & Data Akun (Kanan) --}}
    <div class="profil-grid">

        {{-- =========================================================
             KARTU KIRI: IDENTITAS AKUN & RINGKASAN CALON SISWA
             ========================================================= --}}
        <div class="profil-card p-6 flex flex-col">
            
            {{-- Bagian Atas: Avatar Inisial & Identitas Akun --}}
            <div class="flex flex-col items-center text-center">
                {{-- Avatar inisial 80px (latar hijau muda lembut, inisial hijau tua) --}}
                <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-800 border-2 border-emerald-200 flex items-center justify-center text-3xl font-bold select-none shadow-xs mb-3.5" aria-hidden="true">
                    {{ $initialAkun }}
                </div>

                {{-- Nama Akun --}}
                <h2 class="text-xl sm:text-[22px] font-bold text-slate-900 leading-tight">
                    {{ Str::title($namaAkun) }}
                </h2>

                {{-- Label Peran --}}
                <p class="text-sm text-slate-600 font-medium mt-1">
                    Akun Orang Tua / Wali
                </p>
            </div>

            {{-- Pemisah Tipis --}}
            <hr class="my-5 border-slate-100">

            {{-- Blok Ringkasan Calon Siswa --}}
            <div class="space-y-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block">
                    Calon Siswa
                </span>

                @if(!empty($namaSiswa))
                    <div>
                        <h3 class="text-base sm:text-[17px] font-bold text-slate-900 leading-snug">
                            {{ Str::title($namaSiswa) }}
                        </h3>

                        {{-- Nomor Registrasi dengan Tombol Salin --}}
                        @if(!empty($noReg))
                            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                <span class="text-sm font-semibold text-slate-800" id="text-no-reg">{{ $noReg }}</span>
                                <button type="button"
                                        id="btn-copy-reg"
                                        onclick="copyNoReg()"
                                        class="inline-flex items-center gap-1.5 px-2 py-0.5 text-xs font-medium text-slate-700 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-300 rounded-lg transition-colors focus-visible:outline-2 focus-visible:outline-emerald-600 min-h-[30px]"
                                        aria-label="Salin nomor registrasi">
                                    <i class="far fa-copy text-[11px]" id="icon-copy-reg" aria-hidden="true"></i>
                                    <span id="text-copy-btn">Salin</span>
                                </button>
                                <span id="copy-feedback-reg" class="sr-only" aria-live="polite"></span>
                            </div>
                        @endif

                        {{-- Badge Status Pendaftaran --}}
                        <div class="mt-2.5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $statusInfo['badgeClass'] }}">
                                <i class="fas {{ $statusInfo['icon'] }} text-[12px]" aria-hidden="true"></i>
                                <span>{{ $statusInfo['label'] }}</span>
                            </span>
                        </div>

                        @if(!$isArchived && $rawStatus === 'Ditolak' && !empty($casis?->catatan_pendaftaran))
                            <div class="mt-2.5 text-xs text-rose-900 bg-rose-50 border border-rose-200 rounded-xl p-2.5 leading-relaxed">
                                <i class="fas fa-exclamation-triangle text-rose-700 mr-1" aria-hidden="true"></i>
                                <strong>Catatan Perbaikan:</strong> {{ $casis->catatan_pendaftaran }}
                            </div>
                        @endif

                        @if(!empty($isArchived))
                            <div class="mt-2.5 text-xs text-amber-900 bg-amber-50 border border-amber-200 rounded-xl p-2.5 leading-relaxed">
                                <i class="fas fa-info-circle text-amber-700 mr-1" aria-hidden="true"></i>
                                Data pendaftaran Anda sedang diarsipkan. Silakan hubungi panitia.
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600">
                        Belum ada data calon siswa yang terdaftar.
                    </div>
                @endif
            </div>

            {{-- Tombol Tautan ke Halaman Pendaftaran --}}
            <div class="mt-6 pt-2">
                <a href="{{ route('formcasis') }}"
                   class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors min-h-[44px] focus-visible:outline-2 focus-visible:outline-blue-600">
                    <i class="fas fa-address-card text-sm" aria-hidden="true"></i>
                    <span>Lihat Data Pendaftaran</span>
                </a>
            </div>

        </div>

        {{-- =========================================================
             KARTU KANAN: DATA AKUN
             ========================================================= --}}
        <div class="profil-card">
            
            {{-- Header Kartu --}}
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-900 leading-snug">
                    Data Akun
                </h3>
                <p class="text-sm text-slate-500 mt-1">
                    Data yang Anda gunakan untuk masuk dan dihubungi panitia.
                </p>
            </div>

            {{-- Body: Daftar Label - Nilai Biasa (Bukan Kotak Input) --}}
            <div class="p-6">
                <dl class="divide-y divide-slate-100">
                    
                    {{-- Nama Akun --}}
                    <div class="profil-row-item">
                        <dt class="profil-row-label">Nama Akun</dt>
                        <dd class="profil-row-val">
                            {{ $namaAkun }}
                        </dd>
                    </div>

                    {{-- Email Akun --}}
                    <div class="profil-row-item">
                        <dt class="profil-row-label">Email</dt>
                        <dd class="profil-row-val font-mono text-[15px] break-all">
                            {{ $user->email ?? '-' }}
                        </dd>
                    </div>

                    {{-- Nomor HP / WhatsApp Akun --}}
                    <div class="profil-row-item">
                        <dt class="profil-row-label">No. HP / WhatsApp</dt>
                        <dd class="profil-row-val">
                            @if(!empty($nomorHpFormatted))
                                <span class="inline-flex items-center gap-2 justify-start sm:justify-end tabular-nums">
                                    <span class="w-[18px] h-[18px] flex items-center justify-center shrink-0" aria-hidden="true">
                                        <i class="fab fa-whatsapp text-emerald-600 text-[18px]"></i>
                                    </span>
                                    <span>{{ $nomorHpFormatted }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-2 justify-start sm:justify-end text-sm font-normal">
                                    <span class="text-slate-600">Belum diisi</span>
                                    <button type="button"
                                            @click="openEdit()"
                                            class="inline-flex items-center text-xs font-semibold text-emerald-700 hover:text-emerald-800 underline underline-offset-2 hover:no-underline focus-visible:outline-2 focus-visible:outline-emerald-600"
                                            aria-label="Tambahkan nomor HP">
                                        Tambahkan
                                    </button>
                                </span>
                            @endif
                        </dd>
                    </div>

                    {{-- Peran --}}
                    <div class="profil-row-item">
                        <dt class="profil-row-label">Peran</dt>
                        <dd class="profil-row-val">
                            Orang Tua / Wali
                        </dd>
                    </div>

                </dl>

                {{-- Bar Informasi: Data Calon Siswa Dikelola di Menu Pendaftaran --}}
                <div class="p-4 bg-blue-50/80 border border-blue-200 rounded-xl flex items-center gap-3 mt-6">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0" aria-hidden="true" style="width: 36px; height: 36px; min-width: 36px; min-height: 36px;">
                        <i class="fas fa-info-circle text-base"></i>
                    </div>
                    <p class="text-sm text-blue-900 leading-relaxed m-0">
                        Data calon siswa dikelola di menu <a href="{{ route('formcasis') }}" class="font-semibold text-blue-700 hover:text-blue-900 underline focus-visible:outline-2 focus-visible:outline-blue-600">Pendaftaran</a>.
                    </p>
                </div>

                {{-- Tombol Aksi: Ubah Data Akun & Ubah Kata Sandi --}}
                <div class="mt-6 pt-5 border-t border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <button type="button"
                            x-ref="btnOpenEdit"
                            @click="openEdit()"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors min-h-[44px] focus-visible:outline-2 focus-visible:outline-emerald-600">
                        <i class="fas fa-user-edit text-sm" aria-hidden="true"></i>
                        <span>Ubah Data Akun</span>
                    </button>
                    <button type="button"
                            x-ref="btnOpenPassword"
                            @click="openPassword()"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 text-sm font-semibold border border-slate-300 rounded-xl shadow-xs transition-colors min-h-[44px] focus-visible:outline-2 focus-visible:outline-slate-600">
                        <i class="fas fa-key text-sm text-slate-500" aria-hidden="true"></i>
                        <span>Ubah Kata Sandi</span>
                    </button>
                </div>

            </div>
        </div>

    </div>

    {{-- =========================================================
         MODAL 1: UBAH DATA AKUN
         ========================================================= --}}
    <div x-show="openModalEdit"
         x-cloak
         class="profil-modal-overlay"
         role="dialog"
         aria-modal="true"
         aria-labelledby="modal-edit-title"
         @click.self="closeModalEdit()">
        
        <div class="profil-modal-container">
            
            {{-- Modal Header --}}
            <div class="profil-modal-header">
                <div>
                    <h3 class="text-lg font-bold text-slate-900" id="modal-edit-title">
                        Ubah Data Akun
                    </h3>
                    <p class="text-sm text-slate-600 mt-1">
                        Perbarui nama akun, alamat email, atau nomor HP Anda.
                    </p>
                </div>
                <button type="button"
                        @click="closeModalEdit()"
                        class="profil-btn-close"
                        aria-label="Tutup">
                    <i class="fas fa-times text-[20px]" aria-hidden="true"></i>
                </button>
            </div>

            {{-- Modal Form --}}
            <form method="POST" action="{{ route('profil.casis.update') }}" class="flex flex-col m-0">
                @csrf
                @method('PUT')

                <div class="profil-modal-body">
                    {{-- Field: Nama Akun --}}
                    <div>
                        <label for="input_nama" class="block text-sm font-semibold text-slate-800 mb-1.5">
                            Nama Akun <span class="text-rose-600 font-semibold" aria-hidden="true">*</span>
                        </label>
                        <input type="text"
                               name="nama"
                               id="input_nama"
                               required
                               maxlength="100"
                               autocomplete="name"
                               x-model="inputNama"
                               class="profil-input-field @error('nama') input-error @enderror"
                               placeholder="Nama lengkap akun Anda">
                        @error('nama')
                            <p class="mt-1.5 text-sm text-rose-600 font-medium" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Field: Email Akun --}}
                    <div>
                        <label for="input_email" class="block text-sm font-semibold text-slate-800 mb-1.5">
                            Alamat Email <span class="text-rose-600 font-semibold" aria-hidden="true">*</span>
                        </label>
                        <input type="email"
                               name="email"
                               id="input_email"
                               required
                               maxlength="100"
                               autocomplete="email"
                               inputmode="email"
                               x-model="editEmail"
                               class="profil-input-field @error('email') input-error @enderror"
                               placeholder="nama@email.com">
                        @error('email')
                            <p class="mt-1.5 text-sm text-rose-600 font-medium" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Field: Password Saat Ini (muncul/wajib jika email diubah) --}}
                    <div x-show="editEmail.toLowerCase().trim() !== initialEmail"
                         class="p-4 bg-amber-50 border border-amber-300 rounded-xl space-y-2.5">
                        <div class="flex items-start gap-2.5">
                            <i class="fas fa-shield-alt text-amber-800 text-sm mt-0.5" aria-hidden="true"></i>
                            <p class="text-sm text-amber-950 leading-relaxed font-medium m-0">
                                Anda mengubah alamat email. Demi keamanan, masukkan kata sandi Anda saat ini untuk mengonfirmasi perubahan.
                            </p>
                        </div>
                        <div>
                            <label for="input_password_saat_ini" class="block text-sm font-semibold text-slate-800 mb-1">
                                Kata Sandi Saat Ini <span class="text-rose-600 font-semibold" aria-hidden="true">*</span>
                            </label>
                            <input type="password"
                                   name="password_saat_ini"
                                   id="input_password_saat_ini"
                                   autocomplete="current-password"
                                   class="profil-input-field @error('password_saat_ini') input-error @enderror"
                                   placeholder="Masukkan kata sandi saat ini">
                            @error('password_saat_ini')
                                <p class="mt-1.5 text-sm text-rose-600 font-medium" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Field: Nomor HP / WhatsApp (opsional) --}}
                    <div>
                        <label for="input_no_hp" class="block text-sm font-semibold text-slate-800 mb-1.5">
                            Nomor HP / WhatsApp <span class="font-normal text-slate-500 text-sm">(opsional)</span>
                        </label>
                        <input type="tel"
                               name="no_hp"
                               id="input_no_hp"
                               maxlength="20"
                               autocomplete="tel"
                               inputmode="tel"
                               x-model="inputNoHp"
                               class="profil-input-field @error('no_hp') input-error @enderror"
                               placeholder="Contoh: 081234567890">
                        <p class="text-sm text-slate-600 mt-1.5 leading-relaxed">
                            Nomor ini juga diperbarui di data pendaftaran dan dipakai panitia untuk menghubungi Anda.
                        </p>
                        @error('no_hp')
                            <p class="mt-1.5 text-sm text-rose-600 font-medium" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Modal Footer Actions --}}
                <div class="profil-modal-footer">
                    <button type="button"
                            @click="closeModalEdit()"
                            class="profil-btn-secondary">
                        Batal
                    </button>
                    <button type="submit"
                            class="profil-btn-primary">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- =========================================================
         MODAL 2: GANTI KATA SANDI
         ========================================================= --}}
    <div x-show="openModalPassword"
         x-cloak
         class="profil-modal-overlay"
         role="dialog"
         aria-modal="true"
         aria-labelledby="modal-password-title"
         @click.self="closeModalPassword()">
        
        <div class="profil-modal-container">
            
            {{-- Modal Header --}}
            <div class="profil-modal-header">
                <div>
                    <h3 class="text-lg font-bold text-slate-900" id="modal-password-title">
                        Ubah Kata Sandi
                    </h3>
                    <p class="text-sm text-slate-600 mt-1">
                        Pastikan menggunakan kombinasi kata sandi yang aman minimal 8 karakter.
                    </p>
                </div>
                <button type="button"
                        @click="closeModalPassword()"
                        class="profil-btn-close"
                        aria-label="Tutup">
                    <i class="fas fa-times text-[20px]" aria-hidden="true"></i>
                </button>
            </div>

            {{-- Modal Form --}}
            <form method="POST" action="{{ route('profil.casis.password') }}" class="flex flex-col m-0">
                @csrf
                @method('PUT')

                <div class="profil-modal-body">
                    {{-- Field: Kata Sandi Lama --}}
                    <div x-data="{ show: false }">
                        <label for="input_password_lama" class="block text-sm font-semibold text-slate-800 mb-1.5">
                            Kata Sandi Saat Ini <span class="text-rose-600 font-semibold" aria-hidden="true">*</span>
                        </label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'"
                                   name="password_lama"
                                   id="input_password_lama"
                                   required
                                   autocomplete="current-password"
                                   x-model="inputPassLama"
                                   class="profil-input-field pr-12 @error('password_lama') input-error @enderror"
                                   placeholder="Masukkan kata sandi saat ini">
                            <button type="button"
                                    @click="show = !show"
                                    class="profil-eye-toggle"
                                    :aria-label="show ? 'Sembunyikan kata sandi saat ini' : 'Tampilkan kata sandi saat ini'">
                                <i class="fas text-[18px]" :class="show ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('password_lama')
                            <p class="mt-1.5 text-sm text-rose-600 font-medium" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Field: Kata Sandi Baru --}}
                    <div x-data="{ show: false }">
                        <label for="input_password_baru" class="block text-sm font-semibold text-slate-800 mb-1.5">
                            Kata Sandi Baru <span class="text-rose-600 font-semibold" aria-hidden="true">*</span>
                        </label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'"
                                   name="password_baru"
                                   id="input_password_baru"
                                   required
                                   minlength="8"
                                   autocomplete="new-password"
                                   x-model="inputPassBaru"
                                   class="profil-input-field pr-12 @error('password_baru') input-error @enderror"
                                   placeholder="Minimal 8 karakter">
                            <button type="button"
                                    @click="show = !show"
                                    class="profil-eye-toggle"
                                    :aria-label="show ? 'Sembunyikan kata sandi baru' : 'Tampilkan kata sandi baru'">
                                <i class="fas text-[18px]" :class="show ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
                            </button>
                        </div>
                        <p class="text-sm text-slate-600 mt-1.5 leading-relaxed">
                            Minimal 8 karakter dan berbeda dengan kata sandi saat ini.
                        </p>
                        @error('password_baru')
                            <p class="mt-1.5 text-sm text-rose-600 font-medium" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Field: Konfirmasi Kata Sandi Baru --}}
                    <div x-data="{ show: false }">
                        <label for="input_password_baru_confirmation" class="block text-sm font-semibold text-slate-800 mb-1.5">
                            Konfirmasi Kata Sandi Baru <span class="text-rose-600 font-semibold" aria-hidden="true">*</span>
                        </label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'"
                                   name="password_baru_confirmation"
                                   id="input_password_baru_confirmation"
                                   required
                                   minlength="8"
                                   autocomplete="new-password"
                                   x-model="inputPassKonf"
                                   class="profil-input-field pr-12"
                                   placeholder="Ketik ulang kata sandi baru">
                            <button type="button"
                                    @click="show = !show"
                                    class="profil-eye-toggle"
                                    :aria-label="show ? 'Sembunyikan konfirmasi kata sandi' : 'Tampilkan konfirmasi kata sandi'">
                                <i class="fas text-[18px]" :class="show ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
                            </button>
                        </div>
                        <p class="text-sm text-slate-600 mt-1.5 leading-relaxed">
                            Ulangi kata sandi baru untuk memastikan kecocokan.
                        </p>
                    </div>
                </div>

                {{-- Modal Footer Actions --}}
                <div class="profil-modal-footer">
                    <button type="button"
                            @click="closeModalPassword()"
                            class="profil-btn-secondary">
                        Batal
                    </button>
                    <button type="submit"
                            class="profil-btn-primary">
                        Perbarui Kata Sandi
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

@if(!empty($noReg))
<script>
    function copyNoReg() {
        const noReg = '{{ $noReg }}';
        if (!noReg) return;
        navigator.clipboard.writeText(noReg).then(() => {
            const feedback = document.getElementById('copy-feedback-reg');
            if (feedback) feedback.textContent = 'Nomor registrasi berhasil disalin';
            const textBtn = document.getElementById('text-copy-btn');
            const icon = document.getElementById('icon-copy-reg');
            if (textBtn) textBtn.textContent = 'Tersalin';
            if (icon) icon.className = 'fas fa-check text-emerald-600 text-[11px]';
            setTimeout(() => {
                if (textBtn) textBtn.textContent = 'Salin';
                if (icon) icon.className = 'far fa-copy text-[11px]';
            }, 1500);
        }).catch(() => {
            alert('Nomor registrasi: ' + noReg);
        });
    }
</script>
@endif
@endsection
