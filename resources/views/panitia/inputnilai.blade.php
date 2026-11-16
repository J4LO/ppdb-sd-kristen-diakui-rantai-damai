@extends ('layout.tampilanpanitia')

@php
    $isSudahDinilai = $seleksi && $seleksi->nilai_baca !== null;
    $actionTitle = $isLocked ? 'Detail Nilai Seleksi' : ($isSudahDinilai ? 'Ubah Nilai Seleksi' : 'Input Nilai Seleksi');
    $pageTitleWord = $isLocked ? 'Detail Nilai' : ($isSudahDinilai ? 'Ubah Nilai' : 'Input Nilai');
    $subtitleText = $isLocked ? 'Nilai terkunci setelah hasil diumumkan.' : 'Isi nilai tiga komponen observasi. Hasil ditentukan otomatis.';

    $words = explode(' ', trim($casis->nama_calon_siswa ?? 'Siswa'));
    $initials = '';
    foreach (array_slice($words, 0, 2) as $w) {
        $initials .= strtoupper(mb_substr($w, 0, 1));
    }
    $statusPenilaianLabel = $isLocked ? 'Sudah Diumumkan' : ($isSudahDinilai ? 'Sudah Dinilai' : 'Belum Dinilai');
    $statusPenilaianColor = $isLocked ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($isSudahDinilai ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-slate-100 text-slate-700 border-slate-200');
    $statusPenilaianIcon = $isLocked ? 'fa-bullhorn text-emerald-600' : ($isSudahDinilai ? 'fa-check-circle text-amber-600' : 'fa-hourglass-start text-slate-500');
@endphp

@section('page-title', $pageTitleWord)

@section('breadcrumb')
    <span>Portal Panitia</span>
    <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
    <a href="{{ route('nilai') }}" class="hover:text-emerald-700 transition-colors">Penilaian Seleksi</a>
    <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
    <span class="breadcrumb-item active">{{ $pageTitleWord }}</span>
@endsection

@push('styles')
<style>
    .no-spinner::-webkit-inner-spin-button,
    .no-spinner::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .no-spinner {
        -moz-appearance: textfield;
        appearance: textfield;
    }
    .btn-action-primary {
        min-height: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        font-weight: 700;
        font-size: 0.875rem;
        border-radius: 0.75rem;
        background-color: #059669;
        color: #ffffff;
        box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2);
        transition: all 0.15s ease-in-out;
        cursor: pointer;
        border: none;
    }
    .btn-action-primary:hover {
        background-color: #047857;
    }
    .btn-action-secondary {
        min-height: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        font-weight: 600;
        font-size: 0.875rem;
        border-radius: 0.75rem;
        background-color: #ffffff;
        color: #334155;
        border: 1px solid #cbd5e1;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        transition: all 0.15s ease-in-out;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-action-secondary:hover {
        background-color: #f8fafc;
        border-color: #94a3b8;
        color: #1e293b;
    }

    /* Grid Kolom Nilai Observasi: 3 Kolom pada Desktop (>= 900px), 1 Kolom di bawahnya */
    .scores-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.25rem;
    }
    @media (max-width: 899.98px) {
        .scores-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }

    /* Wadah Kotak Input Nilai: Flex container 52px */
    .score-input-box {
        min-height: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 0.75rem;
        padding: 0 8px;
        gap: 8px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .score-input-box:focus-within {
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.2);
    }
    .score-input-box.is-locked {
        background-color: #f8fafc;
        cursor: not-allowed;
    }
    .score-input-box.has-error {
        border-color: #e11d48;
        box-shadow: 0 0 0 2px rgba(225, 29, 72, 0.2);
    }

    /* Wadah Ikon Nilai: 36px di kiri */
    .score-icon-box {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 0.5rem;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        flex-shrink: 0;
    }

    /* Elemen Input Angka Nilai: flex: 1, text-align: left, 20px semibold */
    .score-input-field {
        flex: 1 1 0%;
        min-width: 0;
        width: 100%;
        height: 100%;
        border: none;
        outline: none;
        background: transparent;
        padding: 0 6px;
        font-size: 20px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        text-align: left;
        color: #0f172a;
    }
    .score-input-field::placeholder {
        color: #94a3b8;
        font-weight: 500;
        font-size: 16px;
    }

    /* Elemen Input Tanggal di dalam .score-input-box */
    .date-input-field {
        flex: 1 1 0%;
        min-width: 0;
        width: 100%;
        height: 100%;
        border: none;
        outline: none;
        background: transparent;
        padding: 0 4px;
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
        cursor: pointer;
    }
    .date-input-field:focus {
        outline: none;
    }
    .date-input-field::-webkit-calendar-picker-indicator {
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        opacity: 0.7;
        transition: opacity 0.15s ease-in-out;
    }
    .date-input-field::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
    }
</style>
@endpush

@section('content')
<div class="space-y-6 w-full scroll-mt-20 pt-1"
     x-data="{
         baca: '{{ old('nilai_baca', isset($seleksi->nilai_baca) ? $seleksi->nilai_baca : '') }}',
         tulis: '{{ old('nilai_tulis', isset($seleksi->nilai_tulis) ? $seleksi->nilai_tulis : '') }}',
         hitung: '{{ old('nilai_hitung', isset($seleksi->nilai_hitung) ? $seleksi->nilai_hitung : '') }}',
         tglSeleksi: '{{ old('tgl_seleksi', ($seleksi && $seleksi->tgl_seleksi ? \Carbon\Carbon::parse($seleksi->tgl_seleksi)->format('Y-m-d') : date('Y-m-d'))) }}',
         serverToday: '{{ date('Y-m-d') }}',
         {{-- TODO: Variabel ambang kelulusan diambil dari konfigurasi config('ppdb.seleksi.nilai_minimal_lulus', 150) --}}
         ambangLulus: {{ (int) config('ppdb.seleksi.nilai_minimal_lulus', 150) }},
         showConfirmModal: false,
         isLocked: {{ $isLocked ? 'true' : 'false' }},
         copied: false,

         get isLengkap() {
             let b = parseInt(this.baca);
             let t = parseInt(this.tulis);
             let h = parseInt(this.hitung);
             return this.baca !== '' && this.baca !== null && !isNaN(b) && b >= 0 && b <= 100 &&
                    this.tulis !== '' && this.tulis !== null && !isNaN(t) && t >= 0 && t <= 100 &&
                    this.hitung !== '' && this.hitung !== null && !isNaN(h) && h >= 0 && h <= 100;
         },

         get total() {
             let b = parseInt(this.baca);
             let t = parseInt(this.tulis);
             let h = parseInt(this.hitung);
             b = isNaN(b) ? 0 : Math.max(0, Math.min(100, b));
             t = isNaN(t) ? 0 : Math.max(0, Math.min(100, t));
             h = isNaN(h) ? 0 : Math.max(0, Math.min(100, h));
             return b + t + h;
         },

         get isLulus() {
             return this.total >= this.ambangLulus;
         },

         get progressPercent() {
             let pct = (this.total / 300) * 100;
             return Math.max(0, Math.min(100, Math.round(pct)));
         },

         get selisihText() {
             if (!this.isLengkap) {
                 return 'Isi ketiga nilai (0-100) untuk melihat hasil';
             }
             if (this.total < this.ambangLulus) {
                 return `Kurang ${this.ambangLulus - this.total} poin untuk lulus (batas ${this.ambangLulus})`;
             }
             if (this.total === this.ambangLulus) {
                 return 'Tepat di batas, dinyatakan lulus';
             }
             return `Lebih ${this.total - this.ambangLulus} poin dari batas lulus (${this.ambangLulus})`;
         },

         get tglFormatted() {
             if (!this.tglSeleksi) return 'Pilih tanggal...';
             try {
                 const parts = this.tglSeleksi.split('-');
                 if (parts.length !== 3) return this.tglSeleksi;
                 const year = parseInt(parts[0], 10);
                 const month = parseInt(parts[1], 10) - 1;
                 const day = parseInt(parts[2], 10);
                 const d = new Date(year, month, day);
                 if (isNaN(d.getTime())) return this.tglSeleksi;
                 const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                 const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                 return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
             } catch (e) {
                 return this.tglSeleksi;
             }
         },

         get isHariIni() {
             return this.tglSeleksi === this.serverToday;
         },

         get isKemarin() {
             if (!this.tglSeleksi || !this.serverToday) return false;
             const tParts = this.tglSeleksi.split('-').map(Number);
             const sParts = this.serverToday.split('-').map(Number);
             const tDate = new Date(tParts[0], tParts[1] - 1, tParts[2]);
             const sDate = new Date(sParts[0], sParts[1] - 1, sParts[2]);
             const diffDays = Math.round((sDate - tDate) / (1000 * 60 * 60 * 24));
             return diffDays === 1;
         },

         setHariIni() {
             if (this.isLocked) return;
             this.tglSeleksi = this.serverToday;
             this.$nextTick(() => {
                 const el = document.getElementById('tgl_seleksi');
                 if (el) el.dispatchEvent(new Event('change', { bubbles: true }));
             });
         },

         submitForm() {
             document.getElementById('form-penilaian-seleksi').submit();
         }
     }">

    {{-- Tautan Kembali & Header Halaman --}}
    <div class="space-y-1">
        <div>
            <a href="{{ route('nilai') }}"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 hover:text-emerald-800 transition-colors min-h-[44px] py-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 rounded-lg"
               title="Kembali ke Daftar Penilaian Seleksi">
                <i class="fas fa-arrow-left text-xs"></i>
                <span>Penilaian Seleksi</span>
            </a>
        </div>
        <div>
            <h1 class="text-2xl md:text-3xl text-slate-900 font-bold font-heading tracking-tight">
                {{ $actionTitle }}
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                {{ $subtitleText }}
            </p>
        </div>
    </div>

    {{-- Banner Satu Baris --}}
    @if($isLocked)
    <div class="bg-amber-50 border border-amber-200/90 rounded-xl p-3.5 sm:p-4 flex items-center gap-3 text-amber-900 shadow-xs">
        <div class="w-9 h-9 min-w-[36px] rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 text-base">
            <i class="fas fa-lock"></i>
        </div>
        <p class="text-sm sm:text-[15px] font-medium leading-normal text-amber-950">
            Nilai terkunci karena hasil sudah diumumkan.
        </p>
    </div>
    @else
    <div class="bg-blue-50/90 border border-blue-200 rounded-xl p-3.5 sm:p-4 flex items-center gap-3 text-blue-900 shadow-xs">
        <div class="w-9 h-9 min-w-[36px] rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 text-base">
            <i class="fas fa-info-circle"></i>
        </div>
        <p class="text-sm sm:text-[15px] font-medium leading-normal text-blue-950">
            Nilai belum terlihat oleh calon siswa sampai hasil dipublikasikan di menu
            <a href="{{ route('panitia.pengumuman.seleksi') }}" class="font-bold text-blue-700 underline hover:text-blue-900 decoration-blue-400 transition-colors">
                Publikasi Hasil
            </a>.
        </p>
    </div>
    @endif

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        {{-- Kartu Identitas Siswa (Header Form Card) --}}
        <div class="bg-slate-50/70 border-b border-slate-100 p-5 sm:p-6 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                {{-- Avatar Inisial 48px --}}
                <div class="w-12 h-12 min-w-[48px] rounded-2xl bg-emerald-100 border border-emerald-200/60 text-emerald-800 flex items-center justify-center font-bold text-lg font-heading shrink-0 shadow-xs">
                    {{ $initials ?: 'CS' }}
                </div>

                <div class="min-w-0 flex flex-wrap items-center gap-2.5">
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 font-heading leading-none">
                        {{ $casis->nama_calon_siswa }}
                    </h2>

                    {{-- Nomor Pendaftaran dengan Tombol Salin Sejajar Samping Nama --}}
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 shadow-2xs text-xs font-semibold leading-none">
                        <span>{{ $casis->no_registrasi ?? '-' }}</span>
                        @if(!empty($casis->no_registrasi))
                        <button type="button"
                                @click="navigator.clipboard.writeText('{{ $casis->no_registrasi }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="text-slate-400 hover:text-emerald-700 transition-colors p-0.5 ml-0.5 cursor-pointer focus:outline-none inline-flex items-center"
                                title="Salin No. Pendaftaran">
                            <i :class="copied ? 'fas fa-check text-emerald-600' : 'far fa-copy'"></i>
                        </button>
                        <span x-show="copied" x-cloak class="text-[10px] text-emerald-700 font-bold">Tersalin!</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <form id="form-penilaian-seleksi" method="post" action="{{ route('simpan', ['id' => $casis->id_calon_siswa]) }}" class="p-6 md:p-8 space-y-8">
            @csrf

            {{-- Baris Tanggal Penilaian & Catatan Penilaian Terakhir --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-slate-100 items-start">
                {{-- Blok Tanggal --}}
                <div class="space-y-1.5">
                    <label for="tgl_seleksi" class="block text-sm font-semibold text-slate-800">
                        Tanggal Penilaian Observasi <span class="text-rose-500">*</span>
                    </label>

                    {{-- Kotak Input Tanggal: Seragam dengan score-input-box dan Ikon Kalender Standar --}}
                    <div class="score-input-box {{ $isLocked ? 'is-locked' : '' }} @error('tgl_seleksi') has-error @enderror">
                        <div class="score-icon-box">
                            <i class="fas fa-calendar-alt text-sm"></i>
                        </div>
                        <input type="date"
                               id="tgl_seleksi"
                               name="tgl_seleksi"
                               max="{{ date('Y-m-d') }}"
                               x-model="tglSeleksi"
                               value="{{ old('tgl_seleksi', ($seleksi && $seleksi->tgl_seleksi ? \Carbon\Carbon::parse($seleksi->tgl_seleksi)->format('Y-m-d') : date('Y-m-d'))) }}"
                               @click="try { if (!isLocked && $el.showPicker) $el.showPicker(); } catch (e) {}"
                               class="date-input-field"
                               aria-describedby="tgl-desc"
                               {{ $isLocked ? 'readonly disabled' : 'required' }}>
                    </div>

                    <div class="text-xs text-slate-500 font-medium pt-0.5">
                        <span class="text-slate-500">Format:</span>
                        <strong class="text-emerald-800 font-semibold" x-text="tglFormatted">
                            {{ $seleksi && $seleksi->tgl_seleksi ? \Carbon\Carbon::parse($seleksi->tgl_seleksi)->translatedFormat('l, j M Y') : \Carbon\Carbon::now()->translatedFormat('l, j M Y') }}
                        </strong>
                    </div>

                    @error('tgl_seleksi')
                    <p class="text-xs text-rose-600 font-semibold flex items-center gap-1 mt-1">
                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Kolom Kanan: Keterangan Terakhir Diubah / Dinilai (jika sudah dinilai) --}}
                @if($isSudahDinilai)
                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-800">
                        Catatan Penilaian Terakhir
                    </label>
                    <div class="score-input-box select-none bg-slate-50/70 border-slate-200">
                        <div class="score-icon-box">
                            <i class="fas fa-user-edit text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0 truncate text-[15px] font-medium text-slate-700">
                            <span>Dinilai pada <strong class="text-slate-900 font-semibold">{{ \Carbon\Carbon::parse($seleksi->tgl_seleksi)->translatedFormat('d M Y') }}</strong>@if($seleksi->panitia) oleh <strong class="text-slate-900 font-semibold">{{ $seleksi->panitia->nama_panitia }}</strong>@endif</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 font-medium pt-0.5">Data tersimpan di sistem seleksi.</p>
                </div>
                @else
                <div></div>
                @endif
            </div>

            {{-- Bagian Tiga Nilai Mapel (Grid 3 Kolom >= 900px, 1 Kolom < 900px) --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-tasks text-emerald-600"></i> Komponen Nilai Observasi
                    </h3>
                    <span class="text-xs sm:text-sm text-slate-600 font-semibold bg-slate-100 border border-slate-200/80 px-2.5 py-1 rounded-lg">
                        Bilangan bulat 0-100
                    </span>
                </div>

                <div class="scores-grid">
                    {{-- Nilai Membaca --}}
                    <div class="space-y-1.5">
                        <label for="nilai_baca" class="block text-sm font-semibold text-slate-800">
                            Membaca <span class="text-rose-500">*</span>
                        </label>
                        <div class="score-input-box {{ $isLocked ? 'is-locked' : '' }} @error('nilai_baca') has-error @enderror">
                            <div class="score-icon-box">
                                <i class="fas fa-book-reader text-slate-600 text-sm"></i>
                            </div>
                            <input type="number"
                                   id="nilai_baca"
                                   name="nilai_baca"
                                   min="0"
                                   max="100"
                                   step="1"
                                   inputmode="numeric"
                                   x-model="baca"
                                   value="{{ old('nilai_baca', isset($seleksi->nilai_baca) ? $seleksi->nilai_baca : '') }}"
                                   @focus="$event.target.select()"
                                   @keydown.enter.prevent="document.getElementById('nilai_tulis').focus()"
                                   class="score-input-field no-spinner"
                                   placeholder="0-100"
                                   aria-describedby="desc-nilai-baca"
                                   {{ $isLocked ? 'readonly disabled' : 'required' }}>
                        </div>
                        <p id="desc-nilai-baca" class="text-xs text-slate-500 font-medium">0 - 100</p>
                        @error('nilai_baca')
                        <p class="text-xs text-rose-600 font-semibold flex items-center gap-1 mt-1">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Nilai Menulis --}}
                    <div class="space-y-1.5">
                        <label for="nilai_tulis" class="block text-sm font-semibold text-slate-800">
                            Menulis <span class="text-rose-500">*</span>
                        </label>
                        <div class="score-input-box {{ $isLocked ? 'is-locked' : '' }} @error('nilai_tulis') has-error @enderror">
                            <div class="score-icon-box">
                                <i class="fas fa-pen-nib text-slate-600 text-sm"></i>
                            </div>
                            <input type="number"
                                   id="nilai_tulis"
                                   name="nilai_tulis"
                                   min="0"
                                   max="100"
                                   step="1"
                                   inputmode="numeric"
                                   x-model="tulis"
                                   value="{{ old('nilai_tulis', isset($seleksi->nilai_tulis) ? $seleksi->nilai_tulis : '') }}"
                                   @focus="$event.target.select()"
                                   @keydown.enter.prevent="document.getElementById('nilai_hitung').focus()"
                                   class="score-input-field no-spinner"
                                   placeholder="0-100"
                                   aria-describedby="desc-nilai-tulis"
                                   {{ $isLocked ? 'readonly disabled' : 'required' }}>
                        </div>
                        <p id="desc-nilai-tulis" class="text-xs text-slate-500 font-medium">0 - 100</p>
                        @error('nilai_tulis')
                        <p class="text-xs text-rose-600 font-semibold flex items-center gap-1 mt-1">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Nilai Berhitung --}}
                    <div class="space-y-1.5">
                        <label for="nilai_hitung" class="block text-sm font-semibold text-slate-800">
                            Berhitung <span class="text-rose-500">*</span>
                        </label>
                        <div class="score-input-box {{ $isLocked ? 'is-locked' : '' }} @error('nilai_hitung') has-error @enderror">
                            <div class="score-icon-box">
                                <i class="fas fa-calculator text-slate-600 text-sm"></i>
                            </div>
                            <input type="number"
                                   id="nilai_hitung"
                                   name="nilai_hitung"
                                   min="0"
                                   max="100"
                                   step="1"
                                   inputmode="numeric"
                                   x-model="hitung"
                                   value="{{ old('nilai_hitung', isset($seleksi->nilai_hitung) ? $seleksi->nilai_hitung : '') }}"
                                   @focus="$event.target.select()"
                                   @keydown.enter.prevent="document.getElementById('btn-simpan-penilaian') ? document.getElementById('btn-simpan-penilaian').focus() : null"
                                   class="score-input-field no-spinner"
                                   placeholder="0-100"
                                   aria-describedby="desc-nilai-hitung"
                                   {{ $isLocked ? 'readonly disabled' : 'required' }}>
                        </div>
                        <p id="desc-nilai-hitung" class="text-xs text-slate-500 font-medium">0 - 100</p>
                        @error('nilai_hitung')
                        <p class="text-xs text-rose-600 font-semibold flex items-center gap-1 mt-1">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 2 Kartu Ringkasan Otomatis: Total Nilai & Hasil Otomatis (Ukuran Lebih Ringkas) --}}
            <div class="pt-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Kartu 1: Total Nilai (maks. 300) --}}
                    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] uppercase tracking-wider text-slate-600 font-bold block">
                                Total Nilai (Maks. 300)
                            </span>
                            <div class="flex items-baseline gap-1 font-heading">
                                <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight" x-text="total">0</span>
                                <span class="text-xs font-bold text-slate-500">/ 300</span>
                            </div>
                        </div>

                        {{-- Bilah Progres Tipis (Tinggi 6px) dengan Penanda Ambang 150 --}}
                        <div class="space-y-1">
                            <div class="relative w-full">
                                {{-- Track --}}
                                <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden border border-slate-200/80">
                                    <div class="h-full transition-all duration-300 rounded-full"
                                         :class="!isLengkap ? 'bg-slate-400' : (isLulus ? 'bg-emerald-600' : 'bg-rose-700')"
                                         :style="'width: ' + progressPercent + '%;'">
                                    </div>
                                </div>
                                {{-- Penanda Vertikal di Posisi Ambang (150/300 = 50%) --}}
                                <div class="absolute top-0 bottom-0 left-1/2 -translate-x-1/2 flex items-center pointer-events-none" style="height: 6px;">
                                    <div class="w-1 h-3 bg-slate-800 rounded-full -mt-0.5 shadow-xs"></div>
                                </div>
                            </div>

                            {{-- Keterangan Penanda Ambang --}}
                            <div class="flex justify-between items-center text-[11px] text-slate-600 font-medium pt-0.5">
                                <span>0</span>
                                <span class="font-bold text-slate-800 flex items-center gap-1">
                                    <i class="fas fa-caret-up text-slate-700 text-[11px]"></i>
                                    Batas lulus 150
                                </span>
                                <span>300</span>
                            </div>
                        </div>
                    </div>

                    {{-- Kartu 2: Keputusan Hasil Seleksi Otomatis --}}
                    <div class="rounded-xl p-4 border flex items-center gap-3.5 transition-colors duration-200"
                         :class="!isLengkap ? 'bg-slate-50 border-slate-200' : (isLulus ? 'bg-emerald-50/80 border-emerald-300' : 'bg-rose-50/80 border-rose-300')">
                        {{-- Wadah ikon 40px solid, ikon putih 18px --}}
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 transition-colors shadow-xs"
                             :class="!isLengkap ? 'bg-slate-700 text-white' : (isLulus ? 'bg-emerald-700 text-white' : 'bg-rose-700 text-white')">
                            <i class="fas text-lg"
                               :class="!isLengkap ? 'fa-hourglass-half' : (isLulus ? 'fa-check' : 'fa-times')"></i>
                        </div>
                        <div class="min-w-0 flex-1" aria-live="polite">
                            <span class="text-[11px] uppercase tracking-wider font-bold block mb-0.5"
                                  :class="!isLengkap ? 'text-slate-600' : (isLulus ? 'text-emerald-900' : 'text-rose-900')">
                                Hasil Otomatis
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-xl sm:text-[22px] font-black leading-tight tracking-tight"
                                      :class="!isLengkap ? 'text-slate-800' : (isLulus ? 'text-emerald-950' : 'text-rose-950')"
                                      x-text="!isLengkap ? 'BELUM LENGKAP' : (isLulus ? 'LULUS SELEKSI' : 'TIDAK LULUS')">
                                    BELUM LENGKAP
                                </span>
                            </div>
                            <p class="text-xs font-semibold mt-0.5"
                               :class="!isLengkap ? 'text-slate-600' : (isLulus ? 'text-emerald-800' : 'text-rose-800')"
                               x-text="selisihText">
                                Isi ketiga nilai untuk melihat hasil
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('nilai') }}"
                   class="btn-action-secondary">
                    Batal
                </a>

                @if(!$isLocked)
                <button type="button"
                        id="btn-simpan-penilaian"
                        @click="showConfirmModal = true"
                        class="btn-action-primary">
                    <i class="fas fa-save"></i>
                    <span>Simpan Penilaian</span>
                </button>
                @else
                <button type="button"
                        disabled
                        class="min-h-[44px] h-[44px] px-6 py-2.5 inline-flex items-center justify-center text-sm font-bold text-slate-400 bg-slate-100 border border-slate-200 rounded-xl cursor-not-allowed gap-2"
                        style="min-height: 44px; height: 44px;">
                    <i class="fas fa-lock"></i>
                    <span>Nilai Terkunci</span>
                </button>
                @endif
            </div>
        </form>
    </div>

    {{-- Modal Konfirmasi Simpan Nilai --}}
    <div x-show="showConfirmModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
         @keydown.escape.window="showConfirmModal = false">
        
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-5"
             style="max-width: 440px;"
             @click.away="showConfirmModal = false">
            
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Konfirmasi Simpan Nilai</h3>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Periksa kembali rincian penilaian sebelum menyimpan data evaluasi siswa.
                    </p>
                </div>
            </div>

            {{-- Ringkasan Dalam Modal --}}
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 space-y-2.5 text-sm">
                <div class="flex justify-between pb-2 border-b border-slate-200/70">
                    <span class="text-slate-500">Nama Calon Siswa:</span>
                    <span class="font-bold text-slate-800">{{ $casis->nama_calon_siswa }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Nilai Membaca:</span>
                    <span class="font-bold text-slate-800" x-text="baca || '0'">0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Nilai Menulis:</span>
                    <span class="font-bold text-slate-800" x-text="tulis || '0'">0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Nilai Berhitung:</span>
                    <span class="font-bold text-slate-800" x-text="hitung || '0'">0</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-slate-200/70 font-semibold">
                    <span class="text-slate-700">Total Akumulasi:</span>
                    <span class="text-emerald-700 font-black text-base" x-text="total + ' / 300'">0 / 300</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-slate-200/70">
                    <span class="text-slate-700 font-semibold">Keputusan Otomatis:</span>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-black"
                          :class="!isLengkap ? 'bg-slate-200 text-slate-800 border border-slate-300' : (isLulus ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300')"
                          x-text="!isLengkap ? 'BELUM LENGKAP' : (isLulus ? 'LULUS SELEKSI' : 'TIDAK LULUS')">
                    </span>
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-900 flex items-start gap-2">
                <i class="fas fa-info-circle text-amber-600 mt-0.5 shrink-0"></i>
                <span>Nilai belum dapat dilihat oleh calon siswa sampai hasil seleksi resmi dipublikasikan oleh panitia di menu Publikasi Hasil.</span>
            </div>

            {{-- Tombol Modal: Fokus awal pada Batal --}}
            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button"
                        x-ref="btnBatal"
                        @click="showConfirmModal = false"
                        class="btn-action-secondary">
                    Batal
                </button>
                <button type="button"
                        @click="submitForm()"
                        class="btn-action-primary">
                    <i class="fas fa-check"></i>
                    <span>Ya, Simpan Nilai</span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
