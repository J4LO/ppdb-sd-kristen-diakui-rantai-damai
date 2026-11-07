@extends('layout.tampilancasis')

@section('page-title', 'Pendaftaran')
@section('title', 'Isi / Edit Form Pendaftaran')

@section('content')
<div class="space-y-6">
    
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl text-slate-800 font-bold font-heading">Formulir Pendaftaran Siswa Baru</h1>
        <p class="text-slate-500 text-sm mt-1">Mohon isi data calon siswa dan informasi orang tua/wali dengan lengkap dan benar.</p>
    </div>

    @if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-2xl">
        <h4 class="text-sm font-bold text-red-800 mb-1">Mohon periksa kembali input Anda:</h4>
        <ul class="list-disc pl-5 text-xs text-red-700 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @php
        $casis = $user->calonSiswa ?? $user->casis;
    @endphp

    <form method="post" action="{{ route('prosescasis') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        {{-- Card: Data Akun --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 bg-slate-50 flex items-center gap-2">
                <i class="fas fa-shield-alt text-slate-400"></i>
                <h3 class="font-bold text-slate-800">Informasi Akun Orang Tua (Read-Only)</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Pengguna</label>
                    <input type="text" class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-500 cursor-not-allowed" value="{{ $user->nama ?? $user->name }}" readonly>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Email Terdaftar</label>
                    <input type="email" class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-500 cursor-not-allowed" value="{{ $user->email }}" readonly>
                </div>
            </div>
        </div>

        {{-- Card: Biodata Calon Siswa --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 bg-slate-50 flex items-center gap-2">
                <i class="fas fa-child text-blue-500"></i>
                <h3 class="font-bold text-slate-800">Biodata Calon Siswa</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- NIK --}}
                <div x-data="{ nikVal: '{{ old('nik', $casis?->nik_calon_siswa ?? $casis?->nik ?? '') }}' }">
                    <label for="nik" class="block text-sm font-semibold text-slate-700 mb-2">NIK (Nomor Induk Kependudukan) <span class="text-red-500">*</span></label>
                    <input type="text" name="nik" id="nik" x-model="nikVal"
                           value="{{ old('nik', $casis?->nik_calon_siswa ?? $casis?->nik ?? '') }}" 
                           class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all @error('nik') border-red-400 @enderror" 
                           placeholder="Masukkan 16 digit NIK" maxlength="16" required>
                    <div class="flex justify-between items-center mt-1">
                        <small id="nikError" class="text-red-500 font-medium"></small>
                        <span class="text-xs text-slate-400" x-text="nikVal.length + ' / 16'"></span>
                    </div>
                </div>

                {{-- Nama Lengkap --}}
                <div>
                    <label for="nama" class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap Casis <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama', $casis?->nama_calon_siswa ?? $casis?->nama ?? '') }}" 
                           class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                           placeholder="Sesuai Akta Kelahiran" required>
                </div>

                {{-- Tempat Lahir --}}
                <div>
                    <label for="tempat_lahir" class="block text-sm font-semibold text-slate-700 mb-2">Tempat Lahir <span class="text-red-500">*</span></label>
                    <input type="text" name="tempat_lahir" id="tempat_lahir" value="{{ old('tempat_lahir', $casis?->tmpt_lahir ?? $casis?->tempat_lahir ?? '') }}" 
                           class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                           placeholder="Contoh: Klaten" required>
                </div>

                {{-- Tanggal Lahir --}}
                <div>
                    <label for="tanggal_lahir" class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" 
                           value="{{ old('tanggal_lahir', $casis && $casis->tgl_lahir ? $casis->tgl_lahir->format('Y-m-d') : ($casis?->tanggal_lahir ?? '')) }}" 
                           class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" required>
                </div>

                {{-- Jenis Kelamin --}}
                <div>
                    <label for="jenis_kelamin" class="block text-sm font-semibold text-slate-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="jenis_kelamin" id="jenis_kelamin" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" required>
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="Laki-Laki" {{ old('jenis_kelamin', $casis?->jenis_kelamin) == 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin', $casis?->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                {{-- Agama --}}
                <div>
                    <label for="agama" class="block text-sm font-semibold text-slate-700 mb-2">Agama <span class="text-red-500">*</span></label>
                    <select name="agama" id="agama" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" required>
                        @php $currentAgama = old('agama', $casis?->agama ?? 'Kristen Protestan'); @endphp
                        <option value="Kristen Protestan" {{ $currentAgama == 'Kristen Protestan' ? 'selected' : '' }}>Kristen Protestan</option>
                        <option value="Katolik" {{ $currentAgama == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                        <option value="Islam" {{ $currentAgama == 'Islam' ? 'selected' : '' }}>Islam</option>
                        <option value="Hindu" {{ $currentAgama == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                        <option value="Buddha" {{ $currentAgama == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                        <option value="Konghucu" {{ $currentAgama == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                        <option value="Lainnya" {{ $currentAgama == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                {{-- Status Anak --}}
                <div>
                    <label for="status_anak" class="block text-sm font-semibold text-slate-700 mb-2">Status dalam Keluarga <span class="text-red-500">*</span></label>
                    <select name="status_anak" id="status_anak" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" required>
                        @php $currentStatusAnak = old('status_anak', $casis?->status_anak ?? 'Anak Kandung'); @endphp
                        <option value="Anak Kandung" {{ $currentStatusAnak == 'Anak Kandung' ? 'selected' : '' }}>Anak Kandung</option>
                        <option value="Anak Tiri" {{ $currentStatusAnak == 'Anak Tiri' ? 'selected' : '' }}>Anak Tiri</option>
                        <option value="Anak Angkat" {{ $currentStatusAnak == 'Anak Angkat' ? 'selected' : '' }}>Anak Angkat</option>
                    </select>
                </div>

                {{-- Jumlah Saudara --}}
                <div>
                    <label for="jml_saudara" class="block text-sm font-semibold text-slate-700 mb-2">Jumlah Saudara <span class="text-red-500">*</span></label>
                    <input type="number" name="jml_saudara" id="jml_saudara" min="0" 
                           value="{{ old('jml_saudara', $casis?->jmlh_saudara ?? $casis?->jml_saudara ?? 0) }}" 
                           class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                           placeholder="Contoh: 2" required>
                </div>

                {{-- Alamat Calon Siswa --}}
                <div class="md:col-span-2">
                    <label for="alamat" class="block text-sm font-semibold text-slate-700 mb-2">Alamat Domisili Casis <span class="text-red-500">*</span></label>
                    <input type="text" name="alamat" id="alamat" 
                           value="{{ old('alamat', $casis?->alamat_calon_siswa ?? $casis?->alamat ?? '') }}" 
                           class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" 
                           placeholder="Nama Jalan, RT/RW, Dusun/Kelurahan, Kecamatan, Kota/Kabupaten" required>
                </div>
            </div>
        </div>

        {{-- Card: Data Ayah --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 bg-slate-50 flex items-center gap-2">
                <i class="fas fa-male text-indigo-500"></i>
                <h3 class="font-bold text-slate-800">Data Ayah Kandung / Wali</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nama_ayah" class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap Ayah</label>
                    <input type="text" name="nama_ayah" id="nama_ayah" 
                           value="{{ old('nama_ayah', $casis?->nama_ayah ?? $casis?->nama_ortu ?? '') }}" 
                           class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" 
                           placeholder="Nama Lengkap Ayah">
                </div>

                <div>
                    <label for="pendidikan_ayah" class="block text-sm font-semibold text-slate-700 mb-2">Pendidikan Terakhir Ayah</label>
                    <select name="pendidikan_ayah" id="pendidikan_ayah" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                        <option value="">Pilih Pendidikan</option>
                        @foreach(['SD','SMP','SMA/SMK','D3','S1','S2','S3'] as $jenjang)
                            <option value="{{ $jenjang }}" {{ old('pendidikan_ayah', $casis?->pendidikan_ayah ?? $casis?->pendidikan_ortu) == $jenjang ? 'selected' : '' }}>{{ $jenjang }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="pekerjaan_ayah" class="block text-sm font-semibold text-slate-700 mb-2">Pekerjaan Ayah</label>
                    <input type="text" name="pekerjaan_ayah" id="pekerjaan_ayah" 
                           value="{{ old('pekerjaan_ayah', $casis?->pekerjaan_ayah ?? $casis?->pekerjaan_ortu ?? '') }}" 
                           class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" 
                           placeholder="Contoh: Karyawan Swasta, Wiraswasta, PNS">
                </div>

                <div>
                    <label for="penghasilan_ayah" class="block text-sm font-semibold text-slate-700 mb-2">Penghasilan Bulanan Ayah (Rp)</label>
                    <input type="number" name="penghasilan_ayah" id="penghasilan_ayah" 
                           value="{{ old('penghasilan_ayah', $casis?->penghasilan_ayah ?? $casis?->gaji_ortu ?? '') }}" 
                           class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" 
                           placeholder="Contoh: 3500000">
                </div>
            </div>
        </div>

        {{-- Card: Data Ibu --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 bg-slate-50 flex items-center gap-2">
                <i class="fas fa-female text-rose-500"></i>
                <h3 class="font-bold text-slate-800">Data Ibu Kandung</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nama_ibu" class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap Ibu</label>
                    <input type="text" name="nama_ibu" id="nama_ibu" 
                           value="{{ old('nama_ibu', $casis?->nama_ibu ?? '') }}" 
                           class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 transition-all" 
                           placeholder="Nama Lengkap Ibu">
                </div>

                <div>
                    <label for="pendidikan_ibu" class="block text-sm font-semibold text-slate-700 mb-2">Pendidikan Terakhir Ibu</label>
                    <select name="pendidikan_ibu" id="pendidikan_ibu" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 transition-all">
                        <option value="">Pilih Pendidikan</option>
                        @foreach(['SD','SMP','SMA/SMK','D3','S1','S2','S3'] as $jenjang)
                            <option value="{{ $jenjang }}" {{ old('pendidikan_ibu', $casis?->pendidikan_ibu) == $jenjang ? 'selected' : '' }}>{{ $jenjang }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="pekerjaan_ibu" class="block text-sm font-semibold text-slate-700 mb-2">Pekerjaan Ibu</label>
                    <input type="text" name="pekerjaan_ibu" id="pekerjaan_ibu" 
                           value="{{ old('pekerjaan_ibu', $casis?->pekerjaan_ibu ?? '') }}" 
                           class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 transition-all" 
                           placeholder="Contoh: Ibu Rumah Tangga, Guru, Wirausaha">
                </div>

                <div>
                    <label for="penghasilan_ibu" class="block text-sm font-semibold text-slate-700 mb-2">Penghasilan Bulanan Ibu (Rp)</label>
                    <input type="number" name="penghasilan_ibu" id="penghasilan_ibu" 
                           value="{{ old('penghasilan_ibu', $casis?->penghasilan_ibu ?? '') }}" 
                           class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 transition-all" 
                           placeholder="Contoh: 2500000">
                </div>
            </div>
        </div>

        {{-- Card: Kontak Orang Tua --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 bg-slate-50 flex items-center gap-2">
                <i class="fas fa-phone-alt text-amber-500"></i>
                <h3 class="font-bold text-slate-800">Kontak Orang Tua / Wali</h3>
            </div>
            <div class="p-6">
                <div>
                    <label for="no_hp" class="block text-sm font-semibold text-slate-700 mb-2">No. HP / WhatsApp Aktif <span class="text-red-500">*</span></label>
                    <input type="text" name="no_hp" id="no_hp" 
                           value="{{ old('no_hp', $casis?->no_hp_orang_tua ?? $casis?->no_hp ?? '') }}" 
                           class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all" 
                           placeholder="Contoh: 081234567890" maxlength="20" required>
                    <p class="text-xs text-slate-500 mt-1">Pastikan nomor aktif untuk menerima notifikasi pengumuman dan seleksi.</p>
                </div>
            </div>
        </div>

        {{-- Card: Berkas Upload Dokumen --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 bg-slate-50 flex items-center gap-2">
                <i class="fas fa-folder-open text-purple-500"></i>
                <h3 class="font-bold text-slate-800">Unggah Berkas Persyaratan</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                {{-- Akta Kelahiran --}}
                @php
                    $akteDoc = $casis?->dokumen?->firstWhere('jenis_dokumen', 'Akta Kelahiran');
                    $isAkteValid = $akteDoc && $akteDoc->status_verifikasi === 'Valid';
                    $isAkteInvalid = $akteDoc && $akteDoc->status_verifikasi === 'Tidak Valid';
                @endphp
                <div class="border {{ $isAkteValid ? 'border-emerald-200 bg-emerald-50/30' : ($isAkteInvalid ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200 bg-slate-50/50') }} rounded-xl p-5 transition-colors flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-1 mb-1">
                            <label for="akte" class="block text-sm font-semibold text-slate-800">Akta Kelahiran <span class="text-rose-500">*</span></label>
                            @if($isAkteValid)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-200">
                                    <i class="fas fa-check-circle"></i> Valid
                                </span>
                            @elseif($isAkteInvalid)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full border border-rose-200">
                                    <i class="fas fa-exclamation-triangle"></i> Perlu Perbaikan
                                </span>
                            @elseif($akteDoc)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-200">
                                    <i class="fas fa-clock"></i> Menunggu
                                </span>
                            @endif
                        </div>

                        @if($isAkteInvalid && !empty($akteDoc->catatan))
                            <div class="mb-3 p-2 bg-rose-100/80 border border-rose-200 text-rose-800 text-xs rounded-lg">
                                <strong>Catatan Panitia:</strong> {{ $akteDoc->catatan }}
                            </div>
                        @endif

                        @if($isAkteValid)
                            <p class="text-xs text-slate-500 my-2">Berkas telah diverifikasi dan disetujui. Berkas terkunci dan tidak dapat diubah.</p>
                        @else
                            <p class="text-xs text-slate-500 mb-3">Format PDF, maksimal 5 MB. Foto dokumen dengan aplikasi pemindai lalu simpan sebagai PDF.</p>
                            <input type="file" name="akte" id="akte" accept="application/pdf" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                        @endif
                    </div>
                    @if ($akteDoc)
                    <div class="mt-3 flex items-center justify-between text-xs font-medium {{ $isAkteValid ? 'text-emerald-800 bg-emerald-100/70 border-emerald-200' : ($isAkteInvalid ? 'text-rose-800 bg-rose-100/70 border-rose-200' : 'text-slate-700 bg-slate-100 border-slate-200') }} p-2 rounded-lg border">
                        <span><i class="fas fa-file-pdf mr-1"></i> Berkas Terunggah</span>
                        <a href="{{ route('dokumen.show', $akteDoc->id_dokumen) }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline font-semibold">Lihat Berkas</a>
                    </div>
                    @endif
                </div>

                {{-- Kartu Keluarga --}}
                @php
                    $kkDoc = $casis?->dokumen?->firstWhere('jenis_dokumen', 'Kartu Keluarga');
                    $isKkValid = $kkDoc && $kkDoc->status_verifikasi === 'Valid';
                    $isKkInvalid = $kkDoc && $kkDoc->status_verifikasi === 'Tidak Valid';
                @endphp
                <div class="border {{ $isKkValid ? 'border-emerald-200 bg-emerald-50/30' : ($isKkInvalid ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200 bg-slate-50/50') }} rounded-xl p-5 transition-colors flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-1 mb-1">
                            <label for="kk" class="block text-sm font-semibold text-slate-800">Kartu Keluarga (KK) <span class="text-rose-500">*</span></label>
                            @if($isKkValid)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-200">
                                    <i class="fas fa-check-circle"></i> Valid
                                </span>
                            @elseif($isKkInvalid)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full border border-rose-200">
                                    <i class="fas fa-exclamation-triangle"></i> Perlu Perbaikan
                                </span>
                            @elseif($kkDoc)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-200">
                                    <i class="fas fa-clock"></i> Menunggu
                                </span>
                            @endif
                        </div>

                        @if($isKkInvalid && !empty($kkDoc->catatan))
                            <div class="mb-3 p-2 bg-rose-100/80 border border-rose-200 text-rose-800 text-xs rounded-lg">
                                <strong>Catatan Panitia:</strong> {{ $kkDoc->catatan }}
                            </div>
                        @endif

                        @if($isKkValid)
                            <p class="text-xs text-slate-500 my-2">Berkas telah diverifikasi dan disetujui. Berkas terkunci dan tidak dapat diubah.</p>
                        @else
                            <p class="text-xs text-slate-500 mb-3">Format PDF, maksimal 5 MB. Foto dokumen dengan aplikasi pemindai lalu simpan sebagai PDF.</p>
                            <input type="file" name="kk" id="kk" accept="application/pdf" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                        @endif
                    </div>
                    @if ($kkDoc)
                    <div class="mt-3 flex items-center justify-between text-xs font-medium {{ $isKkValid ? 'text-emerald-800 bg-emerald-100/70 border-emerald-200' : ($isKkInvalid ? 'text-rose-800 bg-rose-100/70 border-rose-200' : 'text-slate-700 bg-slate-100 border-slate-200') }} p-2 rounded-lg border">
                        <span><i class="fas fa-file-pdf mr-1"></i> Berkas Terunggah</span>
                        <a href="{{ route('dokumen.show', $kkDoc->id_dokumen) }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline font-semibold">Lihat Berkas</a>
                    </div>
                    @endif
                </div>

                {{-- Pas Foto --}}
                @php
                    $fotoDoc = $casis?->dokumen?->firstWhere('jenis_dokumen', 'Pas Foto');
                    $isFotoValid = $fotoDoc && $fotoDoc->status_verifikasi === 'Valid';
                    $isFotoInvalid = $fotoDoc && $fotoDoc->status_verifikasi === 'Tidak Valid';
                @endphp
                <div class="border {{ $isFotoValid ? 'border-emerald-200 bg-emerald-50/30' : ($isFotoInvalid ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200 bg-slate-50/50') }} rounded-xl p-5 transition-colors flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-1 mb-1">
                            <label for="foto" class="block text-sm font-semibold text-slate-800">Pas Foto Calon Siswa <span class="text-rose-500">*</span></label>
                            @if($isFotoValid)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-200">
                                    <i class="fas fa-check-circle"></i> Valid
                                </span>
                            @elseif($isFotoInvalid)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full border border-rose-200">
                                    <i class="fas fa-exclamation-triangle"></i> Perlu Perbaikan
                                </span>
                            @elseif($fotoDoc)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-200">
                                    <i class="fas fa-clock"></i> Menunggu
                                </span>
                            @endif
                        </div>

                        @if($isFotoInvalid && !empty($fotoDoc->catatan))
                            <div class="mb-3 p-2 bg-rose-100/80 border border-rose-200 text-rose-800 text-xs rounded-lg">
                                <strong>Catatan Panitia:</strong> {{ $fotoDoc->catatan }}
                            </div>
                        @endif

                        @if($isFotoValid)
                            <p class="text-xs text-slate-500 my-2">Pas foto telah diverifikasi dan disetujui. Berkas terkunci dan tidak dapat diubah.</p>
                        @else
                            <p class="text-xs text-slate-500 mb-3">Format JPG, JPEG, atau PNG, maksimal 2 MB. Pas foto formal calon siswa.</p>
                            <input type="file" name="foto" id="foto" accept="image/jpeg,image/png" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                        @endif
                    </div>
                    @if ($fotoDoc)
                    <div class="mt-3 flex items-center justify-between text-xs font-medium {{ $isFotoValid ? 'text-emerald-800 bg-emerald-100/70 border-emerald-200' : ($isFotoInvalid ? 'text-rose-800 bg-rose-100/70 border-rose-200' : 'text-slate-700 bg-slate-100 border-slate-200') }} p-2 rounded-lg border">
                        <span><i class="fas fa-image mr-1"></i> Foto Terunggah</span>
                        <a href="{{ route('dokumen.show', $fotoDoc->id_dokumen) }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline font-semibold">Lihat Foto</a>
                    </div>
                    @endif
                </div>

                {{-- KPS / KIP (Opsional) --}}
                @php
                    $kpsDoc = $casis?->dokumen?->firstWhere('jenis_dokumen', 'KPS');
                    $isKpsValid = $kpsDoc && $kpsDoc->status_verifikasi === 'Valid';
                    $isKpsInvalid = $kpsDoc && $kpsDoc->status_verifikasi === 'Tidak Valid';
                @endphp
                <div class="border {{ $isKpsValid ? 'border-emerald-200 bg-emerald-50/30' : ($isKpsInvalid ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200 bg-slate-50/50') }} rounded-xl p-5 transition-colors flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-1 mb-1">
                            <label for="kps" class="block text-sm font-semibold text-slate-800">Kartu KPS / KIP</label>
                            <span class="text-[11px] font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">Opsional</span>
                        </div>

                        @if($isKpsValid)
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-200 mb-2">
                                <i class="fas fa-check-circle"></i> Valid
                            </span>
                        @elseif($isKpsInvalid)
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full border border-rose-200 mb-2">
                                <i class="fas fa-exclamation-triangle"></i> Perlu Perbaikan
                            </span>
                        @elseif($kpsDoc)
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-200 mb-2">
                                <i class="fas fa-clock"></i> Menunggu
                            </span>
                        @endif

                        @if($isKpsInvalid && !empty($kpsDoc->catatan))
                            <div class="mb-3 p-2 bg-rose-100/80 border border-rose-200 text-rose-800 text-xs rounded-lg">
                                <strong>Catatan Panitia:</strong> {{ $kpsDoc->catatan }}
                            </div>
                        @endif

                        @if($isKpsValid)
                            <p class="text-xs text-slate-500 my-2">Berkas telah diverifikasi dan disetujui. Berkas terkunci dan tidak dapat diubah.</p>
                        @else
                            <p class="text-xs text-slate-500 mb-3">Format PDF, maksimal 5 MB. Unggah jika calon siswa memiliki KPS, KIP, atau PKH.</p>
                            <input type="file" name="kps" id="kps" accept="application/pdf" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                        @endif
                    </div>
                    @if ($kpsDoc)
                    <div class="mt-3 flex items-center justify-between text-xs font-medium {{ $isKpsValid ? 'text-emerald-800 bg-emerald-100/70 border-emerald-200' : ($isKpsInvalid ? 'text-rose-800 bg-rose-100/70 border-rose-200' : 'text-slate-700 bg-slate-100 border-slate-200') }} p-2 rounded-lg border">
                        <span><i class="fas fa-file-pdf mr-1"></i> Berkas Terunggah</span>
                        <a href="{{ route('dokumen.show', $kpsDoc->id_dokumen) }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline font-semibold">Lihat Berkas</a>
                    </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- Submit Button --}}
        <div class="flex justify-end pt-4 pb-10">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-600/40 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                <i class="fas fa-save"></i> Simpan Data Pendaftaran
            </button>
        </div>
    </form>
</div>
@endsection
