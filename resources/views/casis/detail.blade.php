@extends('layout.tampilan')

@section('page-title', 'Detail Calon Siswa')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 font-heading">Profil Calon Siswa</h1>
                <p class="text-sm text-slate-500">Rincian data pendaftaran atas nama <span class="font-semibold text-slate-700">{{ $casis->nama_calon_siswa ?? $casis->nama }}</span></p>
            </div>
        </div>
        <div class="hidden sm:block">
            <a href="{{ route('indexcasis') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Kolom Kiri: Data Casis & Orang Tua --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Data Calon Siswa --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                        <i class="fas fa-address-card"></i>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800">Data Calon Siswa</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">NIK</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">{{ $casis->nik_calon_siswa ?: $casis->nik ?: '-' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Nama Lengkap</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">{{ $casis->nama_calon_siswa ?: $casis->nama ?: '-' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tempat Lahir</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">{{ $casis->tmpt_lahir ?: $casis->tempat_lahir ?: '-' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tanggal Lahir</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">
                                {{ $casis->tgl_lahir ? \Carbon\Carbon::parse($casis->tgl_lahir)->translatedFormat('d F Y') : '-' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Jenis Kelamin</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">
                                @if($casis->jenis_kelamin == 'Laki-Laki')
                                    <span class="text-blue-600"><i class="fas fa-mars mr-1"></i> Laki-Laki</span>
                                @elseif($casis->jenis_kelamin == 'Perempuan')
                                    <span class="text-pink-600"><i class="fas fa-venus mr-1"></i> Perempuan</span>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Agama</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">{{ $casis->agama ?: '-' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Status dalam Keluarga</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">{{ $casis->status_anak ?: '-' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Jumlah Saudara</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">{{ $casis->jmlh_saudara ?? $casis->jml_saudara ?? '-' }}</div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Alamat Domisili</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">{{ $casis->alamat_calon_siswa ?: $casis->alamat ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Data Orang Tua --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="fas fa-users"></i>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800">Data Orang Tua / Wali</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- Ayah --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Nama Ayah</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">{{ $casis->nama_ayah ?: $casis->nama_ortu ?: '-' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Pekerjaan Ayah</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">
                                {{ $casis->pekerjaan_ayah ?: $casis->pekerjaan_ortu ?: '-' }}
                                @if($casis->pendidikan_ayah || $casis->pendidikan_ortu)
                                    <span class="text-xs text-slate-500">({{ $casis->pendidikan_ayah ?: $casis->pendidikan_ortu }})</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Penghasilan Ayah</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">
                                {{ ($casis->penghasilan_ayah || $casis->gaji_ortu) ? 'Rp ' . number_format($casis->penghasilan_ayah ?: $casis->gaji_ortu, 0, ',', '.') : '-' }}
                            </div>
                        </div>

                        {{-- Ibu --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Nama Ibu</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">{{ $casis->nama_ibu ?: '-' }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Pekerjaan Ibu</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">
                                {{ $casis->pekerjaan_ibu ?: '-' }}
                                @if($casis->pendidikan_ibu)
                                    <span class="text-xs text-slate-500">({{ $casis->pendidikan_ibu }})</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Penghasilan Ibu</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">
                                {{ $casis->penghasilan_ibu ? 'Rp ' . number_format($casis->penghasilan_ibu, 0, ',', '.') : '-' }}
                            </div>
                        </div>

                        {{-- Kontak --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Nomor Telepon / WhatsApp</label>
                            <div class="text-slate-800 font-medium bg-slate-50 px-3 py-2 rounded-lg border border-slate-100 flex items-center gap-2">
                                <i class="fab fa-whatsapp text-green-500"></i> {{ $casis->no_hp_orang_tua ?: $casis->no_hp ?: '-' }}
                            </div>
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
                    <h2 class="text-lg font-bold text-slate-800">Berkas Upload</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @php
                            $documents = [
                                'Pas Foto'       => ['label' => 'Pas Foto 3x4', 'icon' => 'fa-image', 'color' => 'text-blue-500', 'bg' => 'bg-blue-50'],
                                'Akta Kelahiran' => ['label' => 'Akte Kelahiran', 'icon' => 'fa-file-contract', 'color' => 'text-green-500', 'bg' => 'bg-green-50'],
                                'Kartu Keluarga' => ['label' => 'Kartu Keluarga', 'icon' => 'fa-id-card', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50'],
                            ];
                        @endphp

                        @foreach($documents as $docType => $docInfo)
                            @php
                                $doc = $casis->dokumen->firstWhere('jenis_dokumen', $docType);
                                $hasDoc = !empty($doc?->file_path);
                                $filePath = $hasDoc ? route('dokumen.show', $doc->id_dokumen) : '#';
                            @endphp
                            <div class="flex items-center justify-between p-3 rounded-xl border {{ $hasDoc ? 'border-slate-200 bg-white' : 'border-dashed border-slate-200 bg-slate-50' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg {{ $hasDoc ? $docInfo['bg'] . ' ' . $docInfo['color'] : 'bg-slate-100 text-slate-400' }} flex items-center justify-center">
                                        <i class="fas {{ $docInfo['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold {{ $hasDoc ? 'text-slate-800' : 'text-slate-500' }}">{{ $docInfo['label'] }}</h4>
                                        <p class="text-xs {{ $hasDoc ? 'text-green-600 font-medium' : 'text-slate-400' }}">
                                            @if($hasDoc)
                                                <i class="fas fa-check-circle mr-0.5"></i> Terunggah ({{ $doc->status_verifikasi }})
                                            @else
                                                Belum diunggah
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                @if($hasDoc)
                                    <a href="{{ $filePath }}" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors" title="Lihat Berkas">
                                        <i class="fas fa-external-link-alt text-xs"></i>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Mobile Back Button --}}
            <div class="sm:hidden">
                <a href="{{ route('indexcasis') }}" class="flex items-center justify-center gap-2 w-full py-3 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-xl font-medium transition-colors">
                    <i class="fas fa-arrow-left"></i> Kembali ke Data Casis
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
