@extends('layout.tampilan')

@section('title', 'Periksa Dokumen: ' . $casis->nama_calon_siswa)
@section('page-title', 'Pemeriksaan Dokumen')

@push('styles')
<style>
    .periksa-page,
    .periksa-page h1, .periksa-page h2, .periksa-page h3,
    .periksa-page h4, .periksa-page p, .periksa-page span,
    .periksa-page dt, .periksa-page dd, .periksa-page a,
    .periksa-page button, .periksa-page select, .periksa-page textarea, .periksa-page input {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
    }
</style>
@endpush

@section('content')
<div class="periksa-page space-y-6">

    {{-- Tombol Kembali & Breadcrumb --}}
    <div class="flex items-center justify-between gap-4">
        <a href="{{ route('datapendaftaran') }}"
           class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-200/80 px-3.5 py-2 rounded-xl shadow-xs transition-colors">
            <i class="fas fa-arrow-left text-xs"></i>
            <span>Kembali ke Antrean Verifikasi</span>
        </a>
    </div>

    {{-- Alert Notifikasi --}}
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl relative flex items-center justify-between shadow-xs" role="alert">
        <div class="flex items-center gap-2.5">
            <i class="fas fa-check-circle text-emerald-600 text-base" aria-hidden="true"></i>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
        <button type="button" @click="show = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-emerald-600 hover:bg-emerald-100/60 transition-colors" aria-label="Tutup pemberitahuan">
            <i class="fas fa-times text-xs" aria-hidden="true"></i>
        </button>
    </div>
    @elseif (session('error') || $errors->any())
    <div x-data="{ show: true }" x-show="show" class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl relative flex items-center justify-between shadow-xs" role="alert">
        <div class="flex items-center gap-2.5">
            <i class="fas fa-exclamation-circle text-rose-600 text-base" aria-hidden="true"></i>
            <div class="text-sm font-medium">
                @if(session('error'))
                    <span>{{ session('error') }}</span>
                @else
                    <span>Mohon periksa input form:</span>
                    <ul class="list-disc pl-4 text-xs mt-1 space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <button type="button" @click="show = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-rose-600 hover:bg-rose-100/60 transition-colors" aria-label="Tutup pemberitahuan">
            <i class="fas fa-times text-xs" aria-hidden="true"></i>
        </button>
    </div>
    @endif

    {{-- Kartu Info Calon Siswa & Status Saat Ini --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 font-heading">
                        {{ $casis->nama_calon_siswa }}
                    </h1>
                    <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-800 border border-slate-200">
                        {{ $casis->no_registrasi ?? 'Belum ada No. Reg' }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-wide uppercase
                        {{ $casis->jenis_kelamin == 'Laki-Laki' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-pink-50 text-pink-600 border border-pink-100' }}">
                        <i class="fas {{ $casis->jenis_kelamin == 'Laki-Laki' ? 'fa-mars' : 'fa-venus' }} mr-1"></i>
                        {{ $casis->jenis_kelamin }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Tahun Ajaran: <strong class="text-slate-700">{{ $casis->tahunAjaran->tahun_ajaran ?? '-' }}</strong> • 
                    Tgl Daftar: <strong class="text-slate-700">{{ \Carbon\Carbon::parse($casis->tgl_pendaftaran)->locale('id')->isoFormat('D MMMM Y') }}</strong>
                </p>
            </div>

            {{-- Status Pendaftaran Badge --}}
            <div class="flex flex-col sm:items-end">
                <span class="text-xs text-slate-500 font-medium mb-1">Status Pendaftaran Saat Ini:</span>
                @if($casis->status_pendaftaran === 'Diterima')
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs sm:text-sm font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs">
                        <i class="fas fa-check-circle text-emerald-600"></i>
                        Data Terverifikasi
                    </span>
                @elseif($casis->status_pendaftaran === 'Ditolak')
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs sm:text-sm font-bold bg-rose-100 text-rose-800 border border-rose-300 shadow-xs">
                        <i class="fas fa-exclamation-circle text-rose-600"></i>
                        Perlu Perbaikan
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs sm:text-sm font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-xs">
                        <i class="fas fa-clock text-amber-600"></i>
                        Menunggu Verifikasi
                    </span>
                @endif
            </div>
        </div>

        {{-- Ringkasan Biodata Siswa & Kontak Ortu --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 text-xs">
            <div>
                <span class="text-slate-400 block font-medium">Tempat, Tanggal Lahir</span>
                <span class="text-slate-800 font-semibold">{{ $casis->tmpt_lahir }}, {{ \Carbon\Carbon::parse($casis->tgl_lahir)->format('d/m/Y') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Nama Orang Tua / Wali</span>
                <span class="text-slate-800 font-semibold">{{ $casis->nama_ayah ?? $casis->user->nama ?? '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">No. HP Orang Tua</span>
                @php $hp = $casis->no_hp_orang_tua ?? $casis->user->no_hp; @endphp
                @if($hp)
                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $hp)) }}" target="_blank" rel="noopener noreferrer" class="text-emerald-700 font-semibold hover:underline inline-flex items-center gap-1">
                        <i class="fab fa-whatsapp"></i> {{ $hp }}
                    </a>
                @else
                    <span class="text-slate-400">-</span>
                @endif
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Alamat</span>
                <span class="text-slate-800 font-semibold line-clamp-1" title="{{ $casis->alamat_calon_siswa }}">{{ $casis->alamat_calon_siswa ?? '-' }}</span>
            </div>
        </div>

        {{-- Jika status ditolak, tampilkan rangkuman catatan pendaftaran --}}
        @if($casis->status_pendaftaran === 'Ditolak' && !empty($casis->catatan_pendaftaran))
        <div class="mt-4 p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-900 leading-relaxed">
            <div class="flex items-center gap-1.5 font-bold text-rose-800 mb-0.5">
                <i class="fas fa-exclamation-triangle text-rose-600"></i>
                Rangkuman Catatan Perbaikan Dokumen:
            </div>
            <p class="m-0">{{ $casis->catatan_pendaftaran }}</p>
        </div>
        @endif
    </div>

    {{-- Seksi Dokumen Persyaratan --}}
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fas fa-folder-open text-blue-600"></i>
                Berkas Dokumen Persyaratan
            </h2>
            <span class="text-xs text-slate-500">
                Dokumen wajib: Akta Kelahiran, Kartu Keluarga, Pas Foto.
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- 1. AKTA KELAHIRAN --}}
            @php
                $docAkta = $docMap['Akta Kelahiran'];
                $isPdfAkta = $docAkta && Str::endsWith(strtolower($docAkta->file_path), '.pdf');
            @endphp
            <div class="bg-white rounded-2xl border {{ $docAkta && $docAkta->status_verifikasi === 'Valid' ? 'border-emerald-300' : ($docAkta && $docAkta->status_verifikasi === 'Tidak Valid' ? 'border-rose-300' : 'border-slate-200/80') }} p-5 shadow-xs flex flex-col justify-between"
                 x-data="{ showRejectForm: false }">
                <div>
                    {{-- Header Kartu Dokumen --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-file-invoice text-blue-600 text-base"></i>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">1. Akta Kelahiran</h3>
                                <span class="text-[11px] text-rose-600 font-semibold">Wajib</span>
                            </div>
                        </div>

                        {{-- Badge Status --}}
                        @if(!$docAkta)
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 border border-slate-200">
                                Belum Diunggah
                            </span>
                        @elseif($docAkta->status_verifikasi === 'Valid')
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <i class="fas fa-check mr-1"></i> Valid
                            </span>
                        @elseif($docAkta->status_verifikasi === 'Tidak Valid')
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-rose-100 text-rose-800 border border-rose-300">
                                <i class="fas fa-times mr-1"></i> Tidak Valid
                            </span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-amber-100 text-amber-800 border border-amber-300">
                                <i class="fas fa-clock mr-1"></i> Menunggu Verifikasi
                            </span>
                        @endif
                    </div>

                    {{-- Preview Area --}}
                    @if($docAkta)
                        <div class="my-3">
                            @if($isPdfAkta)
                                <div class="relative bg-slate-50 rounded-xl border border-slate-200 overflow-hidden">
                                    <iframe src="{{ route('dokumen.show', $docAkta->id_dokumen) }}" class="w-full h-80 rounded-xl" title="Preview Akta Kelahiran"></iframe>
                                </div>
                            @else
                                <div class="bg-slate-50 rounded-xl border border-slate-200 p-2 flex justify-center items-center">
                                    <img src="{{ route('dokumen.show', $docAkta->id_dokumen) }}" alt="Preview Akta Kelahiran" class="max-h-80 object-contain rounded-lg">
                                </div>
                            @endif

                            <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                                <span>Diunggah: {{ $docAkta->tgl_upload ? \Carbon\Carbon::parse($docAkta->tgl_upload)->format('d/m/Y H:i') : '-' }}</span>
                                <a href="{{ route('dokumen.show', $docAkta->id_dokumen) }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 font-semibold hover:underline inline-flex items-center gap-1">
                                    <i class="fas fa-external-link-alt text-[10px]"></i> Buka di Tab Baru
                                </a>
                            </div>

                            @if($docAkta->status_verifikasi === 'Tidak Valid' && !empty($docAkta->catatan))
                                <div class="mt-2 p-2.5 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-800">
                                    <strong>Catatan Penolakan:</strong> {{ $docAkta->catatan }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="py-12 text-center text-slate-400 text-xs">
                            <i class="fas fa-file-excel text-3xl mb-2 text-slate-300 block"></i>
                            Calon siswa belum mengunggah Akta Kelahiran.
                        </div>
                    @endif
                </div>

                {{-- Action Toolbar --}}
                @if($docAkta)
                <div class="pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        {{-- Tombol Tandai Valid --}}
                        <form action="{{ route('verifikasidokumen.item', $docAkta->id_dokumen) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="status" value="Valid">
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white text-xs font-semibold rounded-xl shadow-xs transition-all {{ $docAkta->status_verifikasi === 'Valid' ? 'opacity-60 cursor-not-allowed' : '' }}"
                                    {{ $docAkta->status_verifikasi === 'Valid' ? 'disabled' : '' }}>
                                <i class="fas fa-check text-xs"></i>
                                <span>Tandai Valid</span>
                            </button>
                        </form>

                        {{-- Tombol Buka Form Tolak --}}
                        <button type="button" @click="showRejectForm = !showRejectForm"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 active:scale-98 text-white text-xs font-semibold rounded-xl shadow-xs transition-all">
                            <i class="fas fa-times text-xs"></i>
                            <span x-text="showRejectForm ? 'Batal Tolak' : 'Tolak / Perbaikan'"></span>
                        </button>
                    </div>

                    {{-- Form Penolakan (Accordion Alpine) --}}
                    <div x-show="showRejectForm" x-transition class="mt-3 p-3 bg-rose-50 border border-rose-200 rounded-xl">
                        <form action="{{ route('verifikasidokumen.item', $docAkta->id_dokumen) }}" method="POST" class="space-y-2">
                            @csrf
                            <input type="hidden" name="status" value="Tidak Valid">
                            <label class="block text-xs font-bold text-rose-900">
                                Alasan Penolakan Akta Kelahiran <span class="text-rose-600">*</span>:
                            </label>
                            <textarea name="catatan" rows="2" required placeholder="Contoh: Dokumen terpotong atau buram, mohon scan ulang..."
                                      class="w-full bg-white border border-rose-300 rounded-lg p-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showRejectForm = false" class="px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-800">
                                    Batal
                                </button>
                                <button type="submit" class="px-3 py-1 bg-rose-700 hover:bg-rose-800 text-white text-xs font-semibold rounded-lg shadow-xs">
                                    Simpan Penolakan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            </div>

            {{-- 2. KARTU KELUARGA --}}
            @php
                $docKk = $docMap['Kartu Keluarga'];
                $isPdfKk = $docKk && Str::endsWith(strtolower($docKk->file_path), '.pdf');
            @endphp
            <div class="bg-white rounded-2xl border {{ $docKk && $docKk->status_verifikasi === 'Valid' ? 'border-emerald-300' : ($docKk && $docKk->status_verifikasi === 'Tidak Valid' ? 'border-rose-300' : 'border-slate-200/80') }} p-5 shadow-xs flex flex-col justify-between"
                 x-data="{ showRejectForm: false }">
                <div>
                    {{-- Header Kartu Dokumen --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-users text-indigo-600 text-base"></i>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">2. Kartu Keluarga (KK)</h3>
                                <span class="text-[11px] text-rose-600 font-semibold">Wajib</span>
                            </div>
                        </div>

                        {{-- Badge Status --}}
                        @if(!$docKk)
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 border border-slate-200">
                                Belum Diunggah
                            </span>
                        @elseif($docKk->status_verifikasi === 'Valid')
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <i class="fas fa-check mr-1"></i> Valid
                            </span>
                        @elseif($docKk->status_verifikasi === 'Tidak Valid')
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-rose-100 text-rose-800 border border-rose-300">
                                <i class="fas fa-times mr-1"></i> Tidak Valid
                            </span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-amber-100 text-amber-800 border border-amber-300">
                                <i class="fas fa-clock mr-1"></i> Menunggu Verifikasi
                            </span>
                        @endif
                    </div>

                    {{-- Preview Area --}}
                    @if($docKk)
                        <div class="my-3">
                            @if($isPdfKk)
                                <div class="relative bg-slate-50 rounded-xl border border-slate-200 overflow-hidden">
                                    <iframe src="{{ route('dokumen.show', $docKk->id_dokumen) }}" class="w-full h-80 rounded-xl" title="Preview Kartu Keluarga"></iframe>
                                </div>
                            @else
                                <div class="bg-slate-50 rounded-xl border border-slate-200 p-2 flex justify-center items-center">
                                    <img src="{{ route('dokumen.show', $docKk->id_dokumen) }}" alt="Preview Kartu Keluarga" class="max-h-80 object-contain rounded-lg">
                                </div>
                            @endif

                            <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                                <span>Diunggah: {{ $docKk->tgl_upload ? \Carbon\Carbon::parse($docKk->tgl_upload)->format('d/m/Y H:i') : '-' }}</span>
                                <a href="{{ route('dokumen.show', $docKk->id_dokumen) }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 font-semibold hover:underline inline-flex items-center gap-1">
                                    <i class="fas fa-external-link-alt text-[10px]"></i> Buka di Tab Baru
                                </a>
                            </div>

                            @if($docKk->status_verifikasi === 'Tidak Valid' && !empty($docKk->catatan))
                                <div class="mt-2 p-2.5 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-800">
                                    <strong>Catatan Penolakan:</strong> {{ $docKk->catatan }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="py-12 text-center text-slate-400 text-xs">
                            <i class="fas fa-file-excel text-3xl mb-2 text-slate-300 block"></i>
                            Calon siswa belum mengunggah Kartu Keluarga.
                        </div>
                    @endif
                </div>

                {{-- Action Toolbar --}}
                @if($docKk)
                <div class="pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        {{-- Tombol Tandai Valid --}}
                        <form action="{{ route('verifikasidokumen.item', $docKk->id_dokumen) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="status" value="Valid">
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white text-xs font-semibold rounded-xl shadow-xs transition-all {{ $docKk->status_verifikasi === 'Valid' ? 'opacity-60 cursor-not-allowed' : '' }}"
                                    {{ $docKk->status_verifikasi === 'Valid' ? 'disabled' : '' }}>
                                <i class="fas fa-check text-xs"></i>
                                <span>Tandai Valid</span>
                            </button>
                        </form>

                        {{-- Tombol Buka Form Tolak --}}
                        <button type="button" @click="showRejectForm = !showRejectForm"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 active:scale-98 text-white text-xs font-semibold rounded-xl shadow-xs transition-all">
                            <i class="fas fa-times text-xs"></i>
                            <span x-text="showRejectForm ? 'Batal Tolak' : 'Tolak / Perbaikan'"></span>
                        </button>
                    </div>

                    {{-- Form Penolakan (Accordion Alpine) --}}
                    <div x-show="showRejectForm" x-transition class="mt-3 p-3 bg-rose-50 border border-rose-200 rounded-xl">
                        <form action="{{ route('verifikasidokumen.item', $docKk->id_dokumen) }}" method="POST" class="space-y-2">
                            @csrf
                            <input type="hidden" name="status" value="Tidak Valid">
                            <label class="block text-xs font-bold text-rose-900">
                                Alasan Penolakan Kartu Keluarga <span class="text-rose-600">*</span>:
                            </label>
                            <textarea name="catatan" rows="2" required placeholder="Contoh: NIK atau nama calon siswa tidak terbaca jelas..."
                                      class="w-full bg-white border border-rose-300 rounded-lg p-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showRejectForm = false" class="px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-800">
                                    Batal
                                </button>
                                <button type="submit" class="px-3 py-1 bg-rose-700 hover:bg-rose-800 text-white text-xs font-semibold rounded-lg shadow-xs">
                                    Simpan Penolakan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            </div>

            {{-- 3. PAS FOTO --}}
            @php
                $docFoto = $docMap['Pas Foto'];
            @endphp
            <div class="bg-white rounded-2xl border {{ $docFoto && $docFoto->status_verifikasi === 'Valid' ? 'border-emerald-300' : ($docFoto && $docFoto->status_verifikasi === 'Tidak Valid' ? 'border-rose-300' : 'border-slate-200/80') }} p-5 shadow-xs flex flex-col justify-between"
                 x-data="{ showRejectForm: false }">
                <div>
                    {{-- Header Kartu Dokumen --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-portrait text-amber-500 text-base"></i>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">3. Pas Foto Calon Siswa</h3>
                                <span class="text-[11px] text-rose-600 font-semibold">Wajib (JPG/PNG)</span>
                            </div>
                        </div>

                        {{-- Badge Status --}}
                        @if(!$docFoto)
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 border border-slate-200">
                                Belum Diunggah
                            </span>
                        @elseif($docFoto->status_verifikasi === 'Valid')
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <i class="fas fa-check mr-1"></i> Valid
                            </span>
                        @elseif($docFoto->status_verifikasi === 'Tidak Valid')
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-rose-100 text-rose-800 border border-rose-300">
                                <i class="fas fa-times mr-1"></i> Tidak Valid
                            </span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-amber-100 text-amber-800 border border-amber-300">
                                <i class="fas fa-clock mr-1"></i> Menunggu Verifikasi
                            </span>
                        @endif
                    </div>

                    {{-- Preview Area --}}
                    @if($docFoto)
                        <div class="my-3">
                            <div class="bg-slate-50 rounded-xl border border-slate-200 p-3 flex flex-col items-center justify-center">
                                <img src="{{ route('dokumen.show', $docFoto->id_dokumen) }}" alt="Preview Pas Foto" class="max-h-72 object-contain rounded-lg shadow-xs">
                            </div>

                            <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                                <span>Diunggah: {{ $docFoto->tgl_upload ? \Carbon\Carbon::parse($docFoto->tgl_upload)->format('d/m/Y H:i') : '-' }}</span>
                                <a href="{{ route('dokumen.show', $docFoto->id_dokumen) }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 font-semibold hover:underline inline-flex items-center gap-1">
                                    <i class="fas fa-external-link-alt text-[10px]"></i> Buka Foto Penuh
                                </a>
                            </div>

                            @if($docFoto->status_verifikasi === 'Tidak Valid' && !empty($docFoto->catatan))
                                <div class="mt-2 p-2.5 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-800">
                                    <strong>Catatan Penolakan:</strong> {{ $docFoto->catatan }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="py-12 text-center text-slate-400 text-xs">
                            <i class="fas fa-camera text-3xl mb-2 text-slate-300 block"></i>
                            Calon siswa belum mengunggah Pas Foto.
                        </div>
                    @endif
                </div>

                {{-- Action Toolbar --}}
                @if($docFoto)
                <div class="pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        {{-- Tombol Tandai Valid --}}
                        <form action="{{ route('verifikasidokumen.item', $docFoto->id_dokumen) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="status" value="Valid">
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white text-xs font-semibold rounded-xl shadow-xs transition-all {{ $docFoto->status_verifikasi === 'Valid' ? 'opacity-60 cursor-not-allowed' : '' }}"
                                    {{ $docFoto->status_verifikasi === 'Valid' ? 'disabled' : '' }}>
                                <i class="fas fa-check text-xs"></i>
                                <span>Tandai Valid</span>
                            </button>
                        </form>

                        {{-- Tombol Buka Form Tolak --}}
                        <button type="button" @click="showRejectForm = !showRejectForm"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 active:scale-98 text-white text-xs font-semibold rounded-xl shadow-xs transition-all">
                            <i class="fas fa-times text-xs"></i>
                            <span x-text="showRejectForm ? 'Batal Tolak' : 'Tolak / Perbaikan'"></span>
                        </button>
                    </div>

                    {{-- Form Penolakan (Accordion Alpine) --}}
                    <div x-show="showRejectForm" x-transition class="mt-3 p-3 bg-rose-50 border border-rose-200 rounded-xl">
                        <form action="{{ route('verifikasidokumen.item', $docFoto->id_dokumen) }}" method="POST" class="space-y-2">
                            @csrf
                            <input type="hidden" name="status" value="Tidak Valid">
                            <label class="block text-xs font-bold text-rose-900">
                                Alasan Penolakan Pas Foto <span class="text-rose-600">*</span>:
                            </label>
                            <textarea name="catatan" rows="2" required placeholder="Contoh: Foto buram, harap unggah pas foto formal..."
                                      class="w-full bg-white border border-rose-300 rounded-lg p-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showRejectForm = false" class="px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-800">
                                    Batal
                                </button>
                                <button type="submit" class="px-3 py-1 bg-rose-700 hover:bg-rose-800 text-white text-xs font-semibold rounded-lg shadow-xs">
                                    Simpan Penolakan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            </div>

            {{-- 4. KPS (OPSIONAL) --}}
            @php
                $docKps = $docMap['KPS'];
                $isPdfKps = $docKps && Str::endsWith(strtolower($docKps->file_path), '.pdf');
            @endphp
            <div class="bg-white rounded-2xl border {{ $docKps && $docKps->status_verifikasi === 'Valid' ? 'border-emerald-300' : ($docKps && $docKps->status_verifikasi === 'Tidak Valid' ? 'border-rose-300' : 'border-slate-200/80') }} p-5 shadow-xs flex flex-col justify-between"
                 x-data="{ showRejectForm: false }">
                <div>
                    {{-- Header Kartu Dokumen --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-id-card text-purple-600 text-base"></i>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">4. Kartu KPS / KIP</h3>
                                <span class="text-[11px] text-purple-600 font-semibold">Opsional</span>
                            </div>
                        </div>

                        {{-- Badge Status --}}
                        @if(!$docKps)
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-500 border border-slate-200">
                                Tidak Diunggah
                            </span>
                        @elseif($docKps->status_verifikasi === 'Valid')
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <i class="fas fa-check mr-1"></i> Valid
                            </span>
                        @elseif($docKps->status_verifikasi === 'Tidak Valid')
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-rose-100 text-rose-800 border border-rose-300">
                                <i class="fas fa-times mr-1"></i> Tidak Valid
                            </span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-amber-100 text-amber-800 border border-amber-300">
                                <i class="fas fa-clock mr-1"></i> Menunggu Verifikasi
                            </span>
                        @endif
                    </div>

                    {{-- Preview Area --}}
                    @if($docKps)
                        <div class="my-3">
                            @if($isPdfKps)
                                <div class="relative bg-slate-50 rounded-xl border border-slate-200 overflow-hidden">
                                    <iframe src="{{ route('dokumen.show', $docKps->id_dokumen) }}" class="w-full h-80 rounded-xl" title="Preview Kartu KPS"></iframe>
                                </div>
                            @else
                                <div class="bg-slate-50 rounded-xl border border-slate-200 p-2 flex justify-center items-center">
                                    <img src="{{ route('dokumen.show', $docKps->id_dokumen) }}" alt="Preview Kartu KPS" class="max-h-80 object-contain rounded-lg">
                                </div>
                            @endif

                            <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                                <span>Diunggah: {{ $docKps->tgl_upload ? \Carbon\Carbon::parse($docKps->tgl_upload)->format('d/m/Y H:i') : '-' }}</span>
                                <a href="{{ route('dokumen.show', $docKps->id_dokumen) }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 font-semibold hover:underline inline-flex items-center gap-1">
                                    <i class="fas fa-external-link-alt text-[10px]"></i> Buka di Tab Baru
                                </a>
                            </div>

                            @if($docKps->status_verifikasi === 'Tidak Valid' && !empty($docKps->catatan))
                                <div class="mt-2 p-2.5 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-800">
                                    <strong>Catatan Penolakan:</strong> {{ $docKps->catatan }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="py-12 text-center text-slate-400 text-xs">
                            <i class="fas fa-id-card text-3xl mb-2 text-slate-300 block"></i>
                            Calon siswa tidak mengunggah dokumen KPS/KIP (Opsional).
                        </div>
                    @endif
                </div>

                {{-- Action Toolbar --}}
                @if($docKps)
                <div class="pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        {{-- Tombol Tandai Valid --}}
                        <form action="{{ route('verifikasidokumen.item', $docKps->id_dokumen) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="status" value="Valid">
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white text-xs font-semibold rounded-xl shadow-xs transition-all {{ $docKps->status_verifikasi === 'Valid' ? 'opacity-60 cursor-not-allowed' : '' }}"
                                    {{ $docKps->status_verifikasi === 'Valid' ? 'disabled' : '' }}>
                                <i class="fas fa-check text-xs"></i>
                                <span>Tandai Valid</span>
                            </button>
                        </form>

                        {{-- Tombol Buka Form Tolak --}}
                        <button type="button" @click="showRejectForm = !showRejectForm"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 active:scale-98 text-white text-xs font-semibold rounded-xl shadow-xs transition-all">
                            <i class="fas fa-times text-xs"></i>
                            <span x-text="showRejectForm ? 'Batal Tolak' : 'Tolak / Perbaikan'"></span>
                        </button>
                    </div>

                    {{-- Form Penolakan (Accordion Alpine) --}}
                    <div x-show="showRejectForm" x-transition class="mt-3 p-3 bg-rose-50 border border-rose-200 rounded-xl">
                        <form action="{{ route('verifikasidokumen.item', $docKps->id_dokumen) }}" method="POST" class="space-y-2">
                            @csrf
                            <input type="hidden" name="status" value="Tidak Valid">
                            <label class="block text-xs font-bold text-rose-900">
                                Alasan Penolakan Kartu KPS <span class="text-rose-600">*</span>:
                            </label>
                            <textarea name="catatan" rows="2" required placeholder="Tuliskan catatan perbaikan berkas KPS..."
                                      class="w-full bg-white border border-rose-300 rounded-lg p-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showRejectForm = false" class="px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-800">
                                    Batal
                                </button>
                                <button type="submit" class="px-3 py-1 bg-rose-700 hover:bg-rose-800 text-white text-xs font-semibold rounded-lg shadow-xs">
                                    Simpan Penolakan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Kartu Manual Override Status Pendaftaran --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs" x-data="{ openOverride: false }">
        <div class="flex items-center justify-between cursor-pointer" @click="openOverride = !openOverride">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600">
                    <i class="fas fa-tools text-base"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Override Manual Status Pendaftaran</h3>
                    <p class="text-xs text-slate-500">Gunakan opsi ini hanya jika diperlukan keputusan khusus admin yang mengesampingkan status otomatis.</p>
                </div>
            </div>
            <button type="button" class="text-slate-500 hover:text-slate-800 text-sm">
                <i class="fas" :class="openOverride ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>

        <div x-show="openOverride" x-transition class="mt-5 pt-4 border-t border-slate-100">
            <form action="{{ route('verifikasidokumen.override', $casis->id_calon_siswa) }}" method="POST" class="space-y-4 max-w-xl">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Pilih Status Pendaftaran Tujuan <span class="text-rose-500">*</span>:
                    </label>
                    <select name="status_pendaftaran" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" required>
                        <option value="Menunggu" {{ $casis->status_pendaftaran === 'Menunggu' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        <option value="Diterima" {{ $casis->status_pendaftaran === 'Diterima' ? 'selected' : '' }}>Diterima (Data Terverifikasi)</option>
                        <option value="Ditolak" {{ $casis->status_pendaftaran === 'Ditolak' ? 'selected' : '' }}>Ditolak (Perlu Perbaikan)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Alasan / Catatan Override <span class="text-rose-500">*</span>:
                    </label>
                    <textarea name="alasan" rows="3" required placeholder="Tuliskan alasan tertulis mengapa status pendaftaran di-override secara manual..."
                              class="w-full bg-white border border-slate-300 rounded-xl p-3 text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"></textarea>
                    <p class="text-[11px] text-slate-500 mt-1">Alasan wajib diisi dan akan dicatat secara permanen di riwayat log audit.</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            onclick="return confirm('Apakah Anda yakin ingin melakukan override status pendaftaran ini secara manual?')"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-900 active:scale-98 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition-all">
                        <i class="fas fa-save text-xs"></i>
                        <span>Terapkan Override Status</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Riwayat Log Audit Verifikasi --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <i class="fas fa-history text-slate-500"></i>
                Riwayat Log Audit Verifikasi Dokumen
            </h3>
            <span class="text-xs text-slate-400">Total: {{ $logs->count() }} aktivitas</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-2.5">Waktu</th>
                        <th class="px-4 py-2.5">Petugas Admin</th>
                        <th class="px-4 py-2.5">Dokumen / Target</th>
                        <th class="px-4 py-2.5">Perubahan Status</th>
                        <th class="px-4 py-2.5">Catatan / Alasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-500">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-4 py-2.5 whitespace-nowrap font-semibold text-slate-800">
                                {{ $log->admin->nama ?? $log->admin->username ?? 'Sistem / Admin' }}
                            </td>
                            <td class="px-4 py-2.5 whitespace-nowrap font-medium text-slate-700">
                                {{ $log->dokumen->jenis_dokumen ?? 'Status Siswa (Override)' }}
                            </td>
                            <td class="px-4 py-2.5 whitespace-nowrap">
                                <span class="text-slate-500">{{ $log->status_lama ?? 'Belum Ada' }}</span>
                                <i class="fas fa-arrow-right text-[10px] text-slate-400 mx-1"></i>
                                <span class="font-bold {{ $log->status_baru === 'Valid' || $log->status_baru === 'Diterima' ? 'text-emerald-700' : ($log->status_baru === 'Tidak Valid' || $log->status_baru === 'Ditolak' ? 'text-rose-700' : 'text-amber-700') }}">
                                    {{ $log->status_baru }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-slate-600">
                                {{ $log->catatan ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                Belum ada riwayat aktivitas verifikasi untuk calon siswa ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

