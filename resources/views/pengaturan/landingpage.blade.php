@extends('layout.tampilan')

@section('title', 'Pengaturan Foto Landing Page')
@section('page-title', 'Foto Landing Page')

@push('styles')
<style>
    .dropzone-active {
        border-color: #059669 !important;
        background-color: #ecfdf5 !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-6 max-w-6xl mx-auto" x-data="{
    isDragging: false,
    filePreview: null,
    fileName: '',
    fileSize: '',
    isSubmitting: false,
    handleFileSelect(e) {
        const file = e.target.files ? e.target.files[0] : (e.dataTransfer ? e.dataTransfer.files[0] : null);
        if (!file) return;

        if (!file.type.match('image.*')) {
            alert('File yang dipilih harus berupa gambar (JPG, JPEG, PNG, GIF, SVG).');
            return;
        }

        if (file.size > 3 * 1024 * 1024) {
            alert('Ukuran file melebihi batas maksimum 3MB.');
            return;
        }

        this.fileName = file.name;
        this.fileSize = (file.size / (1024 * 1024) < 1) 
            ? (file.size / 1024).toFixed(1) + ' KB' 
            : (file.size / (1024 * 1024)).toFixed(2) + ' MB';

        const reader = new FileReader();
        reader.onload = (event) => {
            this.filePreview = event.target.result;
        };
        reader.readAsDataURL(file);

        if (e.dataTransfer && e.dataTransfer.files) {
            const input = document.getElementById('photo-input');
            if (input) input.files = e.dataTransfer.files;
        }
    },
    clearFile() {
        this.filePreview = null;
        this.fileName = '';
        this.fileSize = '';
        const input = document.getElementById('photo-input');
        if (input) input.value = '';
    }
}">

    {{-- 1. Header Card --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/90 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-start sm:items-center gap-3.5 sm:gap-4">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center text-lg sm:text-xl shrink-0" aria-hidden="true">
                <i class="fas fa-images"></i>
            </div>
            <div>
                <div class="flex items-center gap-1.5 sm:gap-2 text-xs text-slate-500 mb-1 font-medium flex-wrap">
                    <span>Konten Website</span>
                    <i class="fas fa-chevron-right text-[9px] text-slate-400"></i>
                    <span class="text-emerald-700 font-semibold">Landing Page</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
                    Pengaturan Foto Landing Page
                </h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-0.5">
                    Kelola foto visual dan informasi pendaftaran yang ditampilkan pada banner utama (hero section) website PPDB.
                </p>
            </div>
        </div>

        {{-- Tombol Aksi Kanan Atas --}}
        <div class="flex items-center gap-2.5 self-end sm:self-auto w-full sm:w-auto justify-end">
            <a href="{{ url('/') }}"
               target="_blank"
               class="min-h-[44px] px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition-colors inline-flex items-center gap-2 focus-visible:outline-2 focus-visible:outline-emerald-600"
               title="Buka Website Landing Page Utama">
                <i class="fas fa-external-link-alt text-xs text-slate-500"></i>
                <span>Lihat Website</span>
            </a>
        </div>
    </div>

    {{-- 2. Flash Messages & Error Handling --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3.5 rounded-2xl flex items-start gap-3 text-sm shadow-xs" role="alert">
            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fas fa-check text-xs"></i>
            </div>
            <div class="flex-1">
                <p class="font-bold text-emerald-950">Berhasil!</p>
                <p class="text-emerald-800 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-900 px-4 py-3.5 rounded-2xl flex items-start gap-3 text-sm shadow-xs" role="alert">
            <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fas fa-exclamation text-xs"></i>
            </div>
            <div class="flex-1">
                <p class="font-bold text-rose-950">Gagal Mengunggah!</p>
                <p class="text-rose-800 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-900 p-4 rounded-2xl shadow-xs space-y-2" role="alert">
            <div class="flex items-center gap-2 font-bold text-rose-950 text-sm">
                <i class="fas fa-exclamation-triangle text-rose-600"></i>
                <span>Harap perhatikan persyaratan file foto berikut:</span>
            </div>
            <ul class="list-disc list-inside text-xs pl-4 space-y-1 text-rose-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 3. Dua Kartu Sejajar (Height Matching / items-stretch) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        
        {{-- KARTU KIRI: FORM UNGGAH FOTO BARU (7 Kolom di Desktop) --}}
        <div class="lg:col-span-7 bg-white rounded-2xl shadow-sm border border-slate-200/90 overflow-hidden flex flex-col h-full">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold shrink-0">
                        <i class="fas fa-cloud-upload-alt text-xs"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800 leading-snug">Unggah Foto Baru</h2>
                        <p class="text-xs text-slate-500">Pilih file foto dari perangkat Anda untuk dipublikasikan ke halaman utama.</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0 whitespace-nowrap">
                    <i class="fas fa-shield-alt text-[10px]"></i>
                    <span>Maks. 3 MB</span>
                </span>
            </div>

            <form action="{{ route('photo.upload') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  @submit="isSubmitting = true"
                  class="p-5 sm:p-6 space-y-5 flex-1 flex flex-col justify-between">
                @csrf

                {{-- Hidden Native File Input --}}
                <input type="file"
                       id="photo-input"
                       name="photo"
                       accept=".jpg,.jpeg,.png,.gif,.svg"
                       required
                       @change="handleFileSelect($event)"
                       class="sr-only">

                <div class="space-y-4">
                    {{-- Drag & Drop Upload Zone --}}
                    <div @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="isDragging = false; handleFileSelect($event)"
                         :class="{ 'dropzone-active': isDragging }"
                         class="border-2 border-dashed border-slate-300 rounded-2xl p-6 sm:p-7 text-center transition-all bg-slate-50/50 hover:bg-slate-50 hover:border-emerald-400">

                        {{-- Keadaan 1: Belum Memilih File --}}
                        <div x-show="!filePreview" class="space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-2xl shadow-xs">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">
                                    Tarik & lepaskan foto ke sini, atau
                                    <button type="button"
                                            @click="document.getElementById('photo-input').click()"
                                            class="text-emerald-700 hover:text-emerald-800 font-bold underline decoration-2 underline-offset-2 focus:outline-none">
                                        telusuri file
                                    </button>
                                </p>
                                <p class="text-xs text-slate-500 mt-1">
                                    Format didukung: <span class="font-medium text-slate-700">JPEG, PNG, JPG, GIF, SVG</span>
                                </p>
                            </div>
                            <div class="pt-2">
                                <button type="button"
                                        @click="document.getElementById('photo-input').click()"
                                        class="min-h-[44px] px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl border border-slate-300 shadow-xs inline-flex items-center gap-2 transition-all hover:border-slate-400 focus-visible:outline-2 focus-visible:outline-emerald-600">
                                    <i class="fas fa-folder-open text-slate-400"></i>
                                    <span>Pilih File dari Komputer</span>
                                </button>
                            </div>
                        </div>

                        {{-- Keadaan 2: Sudah Memilih File (Live Preview) --}}
                        <div x-show="filePreview" x-cloak class="space-y-4">
                            <div class="relative inline-block max-w-full">
                                <img :src="filePreview"
                                     alt="Pratinjau Foto Baru"
                                     class="max-h-56 mx-auto rounded-xl object-contain border border-slate-200 shadow-sm bg-white">
                                <span class="absolute top-2 right-2 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-700 text-white shadow-sm flex items-center gap-1.5">
                                    <i class="fas fa-check-circle text-[10px]"></i>
                                    <span>Siap Diunggah</span>
                                </span>
                            </div>

                            <div class="bg-white border border-slate-200 rounded-xl p-3 max-w-md mx-auto flex items-center justify-between gap-3 text-left">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                                        <i class="fas fa-file-image text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-slate-800 truncate" x-text="fileName"></p>
                                        <p class="text-[11px] text-slate-400" x-text="fileSize"></p>
                                    </div>
                                </div>
                                <button type="button"
                                        @click="clearFile()"
                                        class="text-xs text-rose-600 hover:text-rose-800 font-semibold p-1.5 rounded-lg hover:bg-rose-50 transition-colors shrink-0"
                                        title="Hapus pilihan file">
                                    <i class="fas fa-times"></i>
                                    <span class="sr-only">Hapus pilihan file</span>
                                </button>
                            </div>

                            <button type="button"
                                    @click="document.getElementById('photo-input').click()"
                                    class="text-xs font-semibold text-slate-600 hover:text-emerald-700 inline-flex items-center gap-1.5 transition-colors">
                                <i class="fas fa-sync-alt text-[11px]"></i>
                                <span>Ganti File Foto Lain</span>
                            </button>
                        </div>

                    </div>

                    {{-- Banner Informasi Penggantian Foto --}}
                    <div class="bg-amber-50/80 border border-amber-200/80 text-amber-900 px-4 py-3 rounded-xl flex items-start gap-3 text-xs leading-relaxed">
                        <i class="fas fa-info-circle text-amber-600 text-sm mt-0.5 shrink-0"></i>
                        <div>
                            <p class="font-semibold text-amber-950">Informasi Pembaruan Banner:</p>
                            <p class="text-amber-800 mt-0.5">
                                Mengunggah foto baru akan otomatis menggantikan foto banner yang sedang aktif di halaman depan, dan status foto sebelumnya akan diubah menjadi non-aktif.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Tombol Submit Form --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit"
                            :disabled="isSubmitting"
                            class="min-h-[44px] w-full sm:w-auto px-6 py-2.5 bg-emerald-700 hover:bg-emerald-800 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-emerald-700/20 inline-flex items-center justify-center gap-2 focus-visible:outline-2 focus-visible:outline-emerald-600">
                        <i class="fas text-xs" :class="isSubmitting ? 'fa-spinner fa-spin' : 'fa-upload'"></i>
                        <span x-text="isSubmitting ? 'Mengunggah Foto...' : 'Unggah & Terapkan Foto'"></span>
                    </button>
                </div>
            </form>
        </div>

        {{-- KARTU KANAN: FOTO BANNER TERKINI (5 Kolom di Desktop) --}}
        <div class="lg:col-span-5 bg-white rounded-2xl shadow-sm border border-slate-200/90 overflow-hidden flex flex-col h-full">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold shrink-0">
                        <i class="fas fa-image text-xs"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800 leading-snug">Foto Terkini</h2>
                        <p class="text-xs text-slate-500">Banner informasi yang saat ini tayang.</p>
                    </div>
                </div>
                @if($latestPhoto)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Aktif</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Default Aktif</span>
                    </span>
                @endif
            </div>

            <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between">
                @if($latestPhoto)
                    <div class="space-y-4">
                        {{-- Foto Display --}}
                        <div class="relative group rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                            <img src="{{ asset('storage/foto/' . $latestPhoto->foto) }}"
                                 alt="Foto Informasi Pendaftaran Terkini"
                                 class="w-full h-56 sm:h-64 object-cover object-center group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                                <a href="{{ asset('storage/foto/' . $latestPhoto->foto) }}"
                                   target="_blank"
                                   class="min-h-[40px] px-3.5 py-2 bg-white/90 hover:bg-white text-slate-800 text-xs font-semibold rounded-lg shadow-sm backdrop-blur-xs inline-flex items-center gap-1.5 transition-colors">
                                    <i class="fas fa-external-link-alt text-[10px]"></i>
                                    <span>Buka Gambar Asli</span>
                                </a>
                            </div>
                        </div>

                        {{-- Metadata Foto --}}
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-2.5 text-xs">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-200/70">
                                <span class="text-slate-500 font-medium">Nama Berkas</span>
                                <span class="font-mono text-slate-800 font-semibold truncate max-w-[200px]" title="{{ $latestPhoto->foto }}">
                                    {{ $latestPhoto->foto }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between pb-2 border-b border-slate-200/70">
                                <span class="text-slate-500 font-medium">Status Tayang</span>
                                <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                                    <i class="fas fa-check-circle text-[11px]"></i>
                                    <span>{{ $latestPhoto->status }}</span>
                                </span>
                            </div>
                            @if($latestPhoto->created_at)
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 font-medium">Waktu Unggah</span>
                                <span class="text-slate-700 font-medium">
                                    {{ \Carbon\Carbon::parse($latestPhoto->created_at)->translatedFormat('d M Y, H:i') }} WIB
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Tombol Aksi Bawah --}}
                    <div class="pt-4">
                        <a href="{{ asset('storage/foto/' . $latestPhoto->foto) }}"
                           target="_blank"
                           class="w-full min-h-[44px] px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition-colors inline-flex items-center justify-center gap-2 focus-visible:outline-2 focus-visible:outline-emerald-600">
                            <i class="fas fa-eye text-xs"></i>
                            <span>Pratinjau Foto Penuh</span>
                        </a>
                    </div>
                @else
                    {{-- Keadaan Menggunakan Slide Bawaan (Default) --}}
                    <div class="space-y-4">
                        {{-- Visual Mockup Slide Bawaan yang Aktif --}}
                        <div class="relative group rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                            <img src="{{ asset('images/hero/slide1.jpeg') }}"
                                 alt="Dokumentasi Default Sekolah"
                                 class="w-full h-48 sm:h-56 object-cover object-center group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent flex flex-col justify-between p-4">
                                <div class="self-start">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-white/90 text-slate-800 shadow-xs backdrop-blur-xs">
                                        <i class="fas fa-images text-emerald-700 text-[10px]"></i>
                                        <span>Slide Bawaan (3 Foto)</span>
                                    </span>
                                </div>
                                <div class="text-white">
                                    <p class="text-xs font-semibold drop-shadow-sm">Dokumentasi PPDB SD Kristen</p>
                                    <p class="text-[11px] text-slate-200 mt-0.5">Berputar bergantian setiap 5 detik</p>
                                </div>
                            </div>
                        </div>

                        {{-- Metadata Informasi Default --}}
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-2.5 text-xs">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-200/70">
                                <span class="text-slate-500 font-medium">Mode Tayang</span>
                                <span class="font-semibold text-slate-800">Slide Dokumentasi Default</span>
                            </div>
                            <div class="flex items-center justify-between pb-2 border-b border-slate-200/70">
                                <span class="text-slate-500 font-medium">Status Banner</span>
                                <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                                    <i class="fas fa-check-circle text-[11px]"></i>
                                    <span>Aktif Berjalan</span>
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 leading-relaxed pt-0.5">
                                Foto banner pendaftaran khusus belum diunggah. Unggah foto baru melalui formulir di samping untuk menampilkan visual khusus pendaftaran.
                            </p>
                        </div>
                    </div>

                    {{-- Tombol Aksi Bawah --}}
                    <div class="pt-4 flex items-center gap-2.5">
                        <button type="button"
                                @click="document.getElementById('photo-input').click()"
                                class="flex-1 min-h-[44px] px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs sm:text-sm font-semibold rounded-xl transition-colors inline-flex items-center justify-center gap-2">
                            <i class="fas fa-plus text-xs"></i>
                            <span>Pilih Foto Baru</span>
                        </button>
                        <a href="{{ url('/') }}"
                           target="_blank"
                           class="min-h-[44px] px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition-colors inline-flex items-center justify-center gap-2">
                            <i class="fas fa-external-link-alt text-xs"></i>
                            <span class="sr-only sm:not-sr-only">Website</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection
