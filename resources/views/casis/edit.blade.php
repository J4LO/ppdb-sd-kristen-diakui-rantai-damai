@extends('layout.tampilan')

@section('page-title', 'Edit Calon Siswa')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-slate-100 gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-user-edit"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 font-heading">Edit Data Pendaftar</h1>
                <p class="text-sm text-slate-500">Perbarui informasi atas nama <span class="font-semibold text-slate-700">{{ $casis->nama_calon_siswa ?? $casis->nama }}</span></p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('indexcasis') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-arrow-left"></i> <span class="hidden sm:inline">Kembali</span>
            </a>
            <button type="button" onclick="document.getElementById('edit-form').submit()" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded-lg text-sm font-medium transition-colors shadow-sm shadow-green-600/20">
                <i class="fas fa-save"></i> <span class="hidden sm:inline">Simpan Perubahan</span>
            </button>
        </div>
    </div>

    <form id="edit-form" method="POST" action="{{ route('updatedata', $casis->id_calon_siswa ?? $casis->id_casis) }}" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Kolom Kiri: Form Data Pokok & Orang Tua --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Data Calon Siswa --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                            <i class="fas fa-address-card"></i>
                        </div>
                        <h2 class="text-lg font-bold text-slate-800">Formulir Data Calon Siswa</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="nik" class="block text-sm font-semibold text-slate-700 mb-1">NIK <span class="text-red-500">*</span></label>
                                <input type="text" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block p-2.5 transition-colors" name="nik" id="nik" value="{{ $casis->nik_calon_siswa ?: $casis->nik }}" required maxlength="16">
                            </div>
                            <div>
                                <label for="nama" class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block p-2.5 transition-colors" name="nama" id="nama" value="{{ $casis->nama_calon_siswa ?: $casis->nama }}" required>
                            </div>
                            <div>
                                <label for="tempat_lahir" class="block text-sm font-semibold text-slate-700 mb-1">Tempat Lahir</label>
                                <input type="text" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block p-2.5 transition-colors" name="tempat_lahir" id="tempat_lahir" value="{{ $casis->tmpt_lahir ?: $casis->tempat_lahir }}">
                            </div>
                            <div>
                                <label for="tanggal_lahir" class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Lahir</label>
                                <input type="date" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block p-2.5 transition-colors" name="tanggal_lahir" id="tanggal_lahir" value="{{ $casis->tgl_lahir ? $casis->tgl_lahir->format('Y-m-d') : ($casis->tanggal_lahir ?? '') }}">
                            </div>
                            <div>
                                <label for="jenis_kelamin" class="block text-sm font-semibold text-slate-700 mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                                <select class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block p-2.5 transition-colors" name="jenis_kelamin" id="jenis_kelamin" required>
                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                    <option value="Laki-Laki" {{ $casis->jenis_kelamin == 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                                    <option value="Perempuan" {{ $casis->jenis_kelamin == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label for="agama" class="block text-sm font-semibold text-slate-700 mb-1">Agama</label>
                                <select class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block p-2.5 transition-colors" name="agama" id="agama">
                                    @php $ag = $casis->agama ?? 'Kristen Protestan'; @endphp
                                    <option value="Kristen Protestan" {{ $ag == 'Kristen Protestan' ? 'selected' : '' }}>Kristen Protestan</option>
                                    <option value="Katolik" {{ $ag == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                    <option value="Islam" {{ $ag == 'Islam' ? 'selected' : '' }}>Islam</option>
                                    <option value="Hindu" {{ $ag == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                    <option value="Buddha" {{ $ag == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                    <option value="Konghucu" {{ $ag == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                                    <option value="Lainnya" {{ $ag == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label for="status_anak" class="block text-sm font-semibold text-slate-700 mb-1">Status Anak</label>
                                <select class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block p-2.5 transition-colors" name="status_anak" id="status_anak">
                                    @php $sa = $casis->status_anak ?? 'Anak Kandung'; @endphp
                                    <option value="Anak Kandung" {{ $sa == 'Anak Kandung' ? 'selected' : '' }}>Anak Kandung</option>
                                    <option value="Anak Tiri" {{ $sa == 'Anak Tiri' ? 'selected' : '' }}>Anak Tiri</option>
                                    <option value="Anak Angkat" {{ $sa == 'Anak Angkat' ? 'selected' : '' }}>Anak Angkat</option>
                                </select>
                            </div>
                            <div>
                                <label for="jml_saudara" class="block text-sm font-semibold text-slate-700 mb-1">Jumlah Saudara</label>
                                <input type="number" min="0" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block p-2.5 transition-colors" name="jml_saudara" id="jml_saudara" value="{{ $casis->jmlh_saudara ?? $casis->jml_saudara ?? 0 }}">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="alamat" class="block text-sm font-semibold text-slate-700 mb-1">Alamat Domisili <span class="text-red-500">*</span></label>
                                <input type="text" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block p-2.5 transition-colors" name="alamat" id="alamat" value="{{ $casis->alamat_calon_siswa ?: $casis->alamat }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Data Ayah & Ibu --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i class="fas fa-users"></i>
                        </div>
                        <h2 class="text-lg font-bold text-slate-800">Formulir Orang Tua / Wali</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            {{-- Ayah --}}
                            <div>
                                <label for="nama_ayah" class="block text-sm font-semibold text-slate-700 mb-1">Nama Ayah</label>
                                <input type="text" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl block p-2.5" name="nama_ayah" id="nama_ayah" value="{{ $casis->nama_ayah ?: $casis->nama_ortu }}">
                            </div>
                            <div>
                                <label for="pendidikan_ayah" class="block text-sm font-semibold text-slate-700 mb-1">Pendidikan Ayah</label>
                                <select class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl block p-2.5" name="pendidikan_ayah" id="pendidikan_ayah">
                                    <option value="">Pilih Pendidikan</option>
                                    @foreach(['SD','SMP','SMA/SMK','D3','S1','S2','S3'] as $jenjang)
                                        <option value="{{ $jenjang }}" {{ ($casis->pendidikan_ayah ?: $casis->pendidikan_ortu) == $jenjang ? 'selected' : '' }}>{{ $jenjang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="pekerjaan_ayah" class="block text-sm font-semibold text-slate-700 mb-1">Pekerjaan Ayah</label>
                                <input type="text" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl block p-2.5" name="pekerjaan_ayah" id="pekerjaan_ayah" value="{{ $casis->pekerjaan_ayah ?: $casis->pekerjaan_ortu }}">
                            </div>
                            <div>
                                <label for="penghasilan_ayah" class="block text-sm font-semibold text-slate-700 mb-1">Penghasilan Ayah (Rp)</label>
                                <input type="number" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl block p-2.5" name="penghasilan_ayah" id="penghasilan_ayah" value="{{ $casis->penghasilan_ayah ?: $casis->gaji_ortu }}">
                            </div>

                            <hr class="sm:col-span-2 border-slate-100">

                            {{-- Ibu --}}
                            <div>
                                <label for="nama_ibu" class="block text-sm font-semibold text-slate-700 mb-1">Nama Ibu</label>
                                <input type="text" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl block p-2.5" name="nama_ibu" id="nama_ibu" value="{{ $casis->nama_ibu }}">
                            </div>
                            <div>
                                <label for="pendidikan_ibu" class="block text-sm font-semibold text-slate-700 mb-1">Pendidikan Ibu</label>
                                <select class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl block p-2.5" name="pendidikan_ibu" id="pendidikan_ibu">
                                    <option value="">Pilih Pendidikan</option>
                                    @foreach(['SD','SMP','SMA/SMK','D3','S1','S2','S3'] as $jenjang)
                                        <option value="{{ $jenjang }}" {{ $casis->pendidikan_ibu == $jenjang ? 'selected' : '' }}>{{ $jenjang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="pekerjaan_ibu" class="block text-sm font-semibold text-slate-700 mb-1">Pekerjaan Ibu</label>
                                <input type="text" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl block p-2.5" name="pekerjaan_ibu" id="pekerjaan_ibu" value="{{ $casis->pekerjaan_ibu }}">
                            </div>
                            <div>
                                <label for="penghasilan_ibu" class="block text-sm font-semibold text-slate-700 mb-1">Penghasilan Ibu (Rp)</label>
                                <input type="number" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl block p-2.5" name="penghasilan_ibu" id="penghasilan_ibu" value="{{ $casis->penghasilan_ibu }}">
                            </div>

                            {{-- Kontak Ortu --}}
                            <div class="sm:col-span-2">
                                <label for="no_hp" class="block text-sm font-semibold text-slate-700 mb-1">No. HP / WhatsApp <span class="text-red-500">*</span></label>
                                <input type="text" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl block p-2.5" name="no_hp" id="no_hp" value="{{ $casis->no_hp_orang_tua ?: $casis->no_hp }}" required>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Kolom Kanan: Berkas Persyaratan --}}
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden sticky top-24">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h2 class="text-lg font-bold text-slate-800">Ganti / Unggah Berkas</h2>
                    </div>
                    <div class="p-6 space-y-5">
                        
                        {{-- Akte --}}
                        @php $akteDoc = $casis->dokumen->firstWhere('jenis_dokumen', 'Akta Kelahiran'); @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-700">Akta Kelahiran (PDF, maks 5 MB)</label>
                                @if($akteDoc)
                                    <a href="{{ route('dokumen.show', $akteDoc->id_dokumen) }}" target="_blank" rel="noopener noreferrer" class="text-xs text-blue-600 underline font-semibold">Lihat</a>
                                @endif
                            </div>
                            @if($akteDoc)
                                <p class="text-xs text-emerald-600 mb-1 flex items-center gap-1">
                                    <i class="fas fa-check-circle"></i> File ada ({{ $akteDoc->status_verifikasi }})
                                </p>
                            @endif
                            <input type="file" name="akte" accept="application/pdf" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                            <p class="text-[11px] text-slate-400 mt-1">Format PDF, maksimal 5 MB.</p>
                        </div>

                        {{-- KK --}}
                        @php $kkDoc = $casis->dokumen->firstWhere('jenis_dokumen', 'Kartu Keluarga'); @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-700">Kartu Keluarga (PDF, maks 5 MB)</label>
                                @if($kkDoc)
                                    <a href="{{ route('dokumen.show', $kkDoc->id_dokumen) }}" target="_blank" rel="noopener noreferrer" class="text-xs text-blue-600 underline font-semibold">Lihat</a>
                                @endif
                            </div>
                            @if($kkDoc)
                                <p class="text-xs text-emerald-600 mb-1 flex items-center gap-1">
                                    <i class="fas fa-check-circle"></i> File ada ({{ $kkDoc->status_verifikasi }})
                                </p>
                            @endif
                            <input type="file" name="kk" accept="application/pdf" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                            <p class="text-[11px] text-slate-400 mt-1">Format PDF, maksimal 5 MB.</p>
                        </div>

                        {{-- Foto --}}
                        @php $fotoDoc = $casis->dokumen->firstWhere('jenis_dokumen', 'Pas Foto'); @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-700">Pas Foto 3x4 (JPG/PNG, maks 2 MB)</label>
                                @if($fotoDoc)
                                    <a href="{{ route('dokumen.show', $fotoDoc->id_dokumen) }}" target="_blank" rel="noopener noreferrer" class="text-xs text-blue-600 underline font-semibold">Lihat</a>
                                @endif
                            </div>
                            @if($fotoDoc)
                                <p class="text-xs text-emerald-600 mb-1 flex items-center gap-1">
                                    <i class="fas fa-check-circle"></i> File ada ({{ $fotoDoc->status_verifikasi }})
                                </p>
                            @endif
                            <input type="file" name="foto" accept="image/jpeg,image/png" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                            <p class="text-[11px] text-slate-400 mt-1">Format JPG, JPEG, atau PNG, maksimal 2 MB.</p>
                        </div>

                        {{-- KPS (Opsional) --}}
                        @php $kpsDoc = $casis->dokumen->firstWhere('jenis_dokumen', 'KPS'); @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-700">Kartu KPS / KIP (Opsional)</label>
                                @if($kpsDoc)
                                    <a href="{{ route('dokumen.show', $kpsDoc->id_dokumen) }}" target="_blank" rel="noopener noreferrer" class="text-xs text-blue-600 underline font-semibold">Lihat</a>
                                @endif
                            </div>
                            @if($kpsDoc)
                                <p class="text-xs text-emerald-600 mb-1 flex items-center gap-1">
                                    <i class="fas fa-check-circle"></i> File ada ({{ $kpsDoc->status_verifikasi }})
                                </p>
                            @endif
                            <input type="file" name="kps" accept="application/pdf" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                            <p class="text-[11px] text-slate-400 mt-1">Format PDF, maksimal 5 MB.</p>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 text-white hover:bg-green-700 rounded-xl text-sm font-medium transition-colors shadow-sm shadow-green-600/20">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
