@extends('layout.tampilan')

@section('title', 'Pendaftaran Calon Siswa Baru')
@section('page-title', 'Tambah Calon Siswa')

@push('styles')
<style>
    .password-toggle-btn {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        background: transparent;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        z-index: 10;
    }
    .password-toggle-btn:hover {
        color: #1e293b;
        background-color: #e2e8f0;
    }
    .password-toggle-btn:focus-visible {
        outline: 2px solid #047857;
        outline-offset: 1px;
    }
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-6 max-w-5xl mx-auto" x-data="{
    showPassword: false,
    nikVal: '{{ old('nik', '') }}',
    get isNikValid() {
        return this.nikVal.length >= 10 && this.nikVal.length <= 16;
    },
    formatNik(e) {
        this.nikVal = e.target.value.replace(/\D/g, '').slice(0, 16);
        e.target.value = this.nikVal;
    },
    formatHp(e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 15);
    }
}">

    {{-- 1. Header Section --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/90 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-start sm:items-center gap-3.5 sm:gap-4">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center text-lg sm:text-xl shrink-0" aria-hidden="true">
                <i class="fas fa-user-plus"></i>
            </div>
            <div>
                <div class="flex items-center gap-1.5 sm:gap-2 text-xs text-slate-500 mb-1 font-medium flex-wrap">
                    <span>Master Data</span>
                    <i class="fas fa-chevron-right text-[9px] text-slate-400"></i>
                    <a href="{{ route('indexcasis') }}" class="hover:text-emerald-700 transition-colors">Calon Siswa</a>
                    <i class="fas fa-chevron-right text-[9px] text-slate-400"></i>
                    <span class="text-emerald-700 font-semibold">Tambah Siswa</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
                    Pendaftaran Calon Siswa Baru
                </h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-0.5">
                    Lengkapi formulir pendaftaran siswa baru oleh Admin (pembuatan akun orang tua sekaligus biodata pendaftaran).
                </p>
            </div>
        </div>

        {{-- Tombol Aksi Header Atas --}}
        <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
            <a href="{{ route('indexcasis') }}"
               class="min-h-[44px] px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition-colors inline-flex items-center justify-center gap-2 flex-1 sm:flex-initial focus-visible:outline-2 focus-visible:outline-emerald-600">
                <i class="fas fa-arrow-left text-xs"></i>
                <span>Kembali</span>
            </a>
            <button type="button"
                    onclick="document.getElementById('form-tambah-siswa').submit()"
                    class="min-h-[44px] px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs sm:text-sm font-semibold rounded-xl transition-all shadow-sm shadow-emerald-700/20 inline-flex items-center justify-center gap-2 flex-1 sm:flex-initial focus-visible:outline-2 focus-visible:outline-emerald-600">
                <i class="fas fa-save text-xs"></i>
                <span>Simpan Siswa</span>
            </button>
        </div>
    </div>

    {{-- 2. Flash Messages & Error Handling --}}
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm font-medium shadow-xs" role="alert">
            <i class="fas fa-exclamation-circle text-rose-600 text-base shrink-0"></i>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl shadow-xs space-y-2" role="alert">
            <div class="flex items-center gap-2 font-bold text-rose-900 text-sm">
                <i class="fas fa-exclamation-triangle text-rose-600"></i>
                <span>Harap periksa kembali beberapa inputan berikut:</span>
            </div>
            <ul class="list-disc list-inside text-xs pl-4 space-y-1 text-rose-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 3. Form Utama Pendaftaran --}}
    <form id="form-tambah-siswa" method="POST" action="{{ route('prosespendaftaran') }}" class="space-y-6">
        @csrf

        {{-- KARTU 1: Akun Akses Orang Tua / Wali --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/90 overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold shrink-0">
                        1
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800 leading-snug">Akun Akses Orang Tua / Wali</h2>
                        <p class="text-xs text-slate-500">Kredensial login untuk memantau status seleksi dan pembayaran daftar ulang.</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 shrink-0 self-start sm:self-auto whitespace-nowrap">
                    <i class="fas fa-user-shield text-[10px]"></i>
                    <span>Role Orang Tua</span>
                </span>
            </div>

            <div class="p-5 sm:p-6 space-y-5">
                {{-- Banner Info Kredensial --}}
                <div class="bg-emerald-50/80 border border-emerald-200/80 text-emerald-900 px-4 py-3 rounded-xl flex items-start gap-3 text-xs leading-relaxed">
                    <i class="fas fa-info-circle text-emerald-600 text-sm mt-0.5 shrink-0"></i>
                    <div>
                        <p class="font-semibold text-emerald-950">Informasi Pembuatan Akun Otomatis:</p>
                        <p class="text-emerald-800 mt-0.5">
                            Akun Orang Tua ini akan otomatis terhubung ke calon siswa yang didaftarkan. Orang tua dapat login ke sistem PPDB menggunakan email dan password yang ditetapkan di bawah ini.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {{-- Username (Opsional) --}}
                    <div class="space-y-1.5">
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Username <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name') }}"
                                   placeholder="Contoh: budi_santoso"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder:text-slate-400">
                        </div>
                        <p class="text-[11px] text-slate-400">Jika dikosongkan, sistem membuat otomatis dari email.</p>
                    </div>

                    {{-- Email --}}
                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Email Orang Tua <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="email"
                                   name="email"
                                   id="email"
                                   value="{{ old('email') }}"
                                   required
                                   placeholder="orangtua@email.com"
                                   class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder:text-slate-400 @error('email') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-envelope text-xs"></i>
                            </div>
                        </div>
                        @error('email')
                            <p class="text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Password Akun <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'"
                                   name="password"
                                   id="password"
                                   required
                                   minlength="6"
                                   placeholder="Minimal 6 karakter..."
                                   class="w-full px-3.5 py-2.5 pr-11 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder:text-slate-400 @error('password') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="password-toggle-btn"
                                    tabindex="-1"
                                    title="Tampilkan / Sembunyikan Password">
                                <i class="fas text-sm" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                        @else
                            <p class="text-[11px] text-slate-400">Minimal 6 karakter alfanumerik.</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 2: Biodata Pokok Calon Siswa --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/90 overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 sm:gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold shrink-0">
                        2
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800 leading-snug">Biodata Pokok Calon Siswa</h2>
                        <p class="text-xs text-slate-500">Identitas diri calon siswa baru yang akan didaftarkan.</p>
                    </div>
                </div>
                <span class="text-xs text-slate-400 font-medium self-start sm:self-auto">Nomor registrasi digenerate otomatis</span>
            </div>

            <div class="p-5 sm:p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- NIK --}}
                    <div class="space-y-1.5">
                        <label for="nik" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            NIK Calon Siswa <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   name="nik"
                                   id="nik"
                                   x-model="nikVal"
                                   @input="formatNik($event)"
                                   required
                                   maxlength="16"
                                   placeholder="16 digit angka (sesuai Kartu Keluarga / Akta)"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder:font-sans placeholder:text-slate-400 @error('nik') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-id-card text-xs"></i>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-400">Harus berupa angka (10-16 digit)</span>
                            <span class="font-mono text-slate-500" x-text="nikVal.length + '/16'"></span>
                        </div>
                        @error('nik')
                            <p class="text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nama Lengkap --}}
                    <div class="space-y-1.5">
                        <label for="nama" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Nama Lengkap Calon Siswa <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   name="nama"
                                   id="nama"
                                   value="{{ old('nama') }}"
                                   required
                                   placeholder="Contoh: Andreas Pratama"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder:text-slate-400 @error('nama') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-user-graduate text-xs"></i>
                            </div>
                        </div>
                        @error('nama')
                            <p class="text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div class="space-y-1.5">
                        <label for="jenis_kelamin" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Jenis Kelamin <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="jenis_kelamin"
                                    id="jenis_kelamin"
                                    required
                                    class="w-full pl-10 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all appearance-none cursor-pointer">
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-Laki" {{ old('jenis_kelamin') === 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-venus-mars text-xs"></i>
                            </div>
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Tempat Lahir --}}
                    <div class="space-y-1.5">
                        <label for="tempat_lahir" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Tempat Lahir
                        </label>
                        <div class="relative">
                            <input type="text"
                                   name="tempat_lahir"
                                   id="tempat_lahir"
                                   value="{{ old('tempat_lahir') }}"
                                   placeholder="Contoh: Mamasa, Makassar"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder:text-slate-400">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-map-marker-alt text-xs"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Tanggal Lahir --}}
                    <div class="space-y-1.5">
                        <label for="tanggal_lahir" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Tanggal Lahir
                        </label>
                        <div class="relative">
                            <input type="date"
                                   name="tanggal_lahir"
                                   id="tanggal_lahir"
                                   value="{{ old('tanggal_lahir') }}"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                        </div>
                    </div>

                    {{-- Alamat Domisili --}}
                    <div class="space-y-1.5 md:col-span-2">
                        <label for="alamat" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Alamat Domisili Tempat Tinggal <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   name="alamat"
                                   id="alamat"
                                   value="{{ old('alamat') }}"
                                   required
                                   placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kabupaten/Kota..."
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder:text-slate-400 @error('alamat') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-home text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 3: Data Orang Tua / Wali & Kontak --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/90 overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold shrink-0">
                        3
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800 leading-snug">Data Orang Tua / Wali & Kontak</h2>
                        <p class="text-xs text-slate-500">Informasi nama orang tua dan nomor telepon/WhatsApp yang aktif dihubungi.</p>
                    </div>
                </div>
            </div>

            <div class="p-5 sm:p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {{-- Nama Orang Tua --}}
                    <div class="space-y-1.5">
                        <label for="nama_ortu" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Nama Orang Tua / Wali <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   name="nama_ortu"
                                   id="nama_ortu"
                                   value="{{ old('nama_ortu') }}"
                                   required
                                   placeholder="Contoh: Yohanes Pratama"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder:text-slate-400 @error('nama_ortu') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-user-friends text-xs"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Pendidikan Terakhir --}}
                    <div class="space-y-1.5">
                        <label for="pendidikan_ortu" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Pendidikan Orang Tua
                        </label>
                        <div class="relative">
                            <select name="pendidikan_ortu"
                                    id="pendidikan_ortu"
                                    class="w-full pl-10 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all appearance-none cursor-pointer">
                                <option value="">-- Pilih Jenjang Pendidikan --</option>
                                @foreach(['Tidak Bersekolah', 'SD', 'SMP', 'SMA/SMK', 'D3', 'S1', 'S2', 'S3'] as $jenjang)
                                    <option value="{{ $jenjang }}" {{ old('pendidikan_ortu') === $jenjang ? 'selected' : '' }}>
                                        {{ $jenjang }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-graduation-cap text-xs"></i>
                            </div>
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    {{-- No. HP / WhatsApp --}}
                    <div class="space-y-1.5">
                        <label for="no_hp" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            No. Telepon / WhatsApp <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="tel"
                                   name="no_hp"
                                   id="no_hp"
                                   value="{{ old('no_hp') }}"
                                   @input="formatHp($event)"
                                   required
                                   placeholder="Contoh: 081234567890"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder:font-sans placeholder:text-slate-400 @error('no_hp') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-600">
                                <i class="fab fa-whatsapp text-sm"></i>
                            </div>
                        </div>
                        @error('no_hp')
                            <p class="text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Footer Tombol Aksi Bawah --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-500 text-center sm:text-left">
                Pastikan data pendaftaran calon siswa terisi dengan benar sebelum menekan tombol simpan.
            </p>
            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <a href="{{ route('indexcasis') }}"
                   class="min-h-[44px] px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors inline-flex items-center justify-center gap-2 w-full sm:w-auto focus-visible:outline-2 focus-visible:outline-emerald-600">
                    Batal
                </a>
                <button type="submit"
                        class="min-h-[44px] px-6 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-emerald-700/20 inline-flex items-center justify-center gap-2 w-full sm:w-auto focus-visible:outline-2 focus-visible:outline-emerald-600">
                    <i class="fas fa-save text-xs"></i>
                    <span>Simpan Pendaftaran</span>
                </button>
            </div>
        </div>

    </form>
</div>
@endsection
