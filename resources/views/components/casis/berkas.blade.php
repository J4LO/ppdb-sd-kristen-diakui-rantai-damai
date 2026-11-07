@php
    $casis = $user->calonSiswa ?? $user->casis;

    // 4 Jenis Dokumen (3 Wajib + 1 Opsional KPS)
    $berkasList = [
        'Akta Kelahiran' => [
            'key' => 'akte',
            'label' => 'Akte Kelahiran',
            'icon' => 'fa-file-signature',
            'color' => 'text-blue-600',
            'bg' => 'bg-blue-100',
            'aspect' => 'thumb-doc',
            'is_required' => true,
        ],
        'Kartu Keluarga' => [
            'key' => 'kk',
            'label' => 'Kartu Keluarga',
            'icon' => 'fa-users',
            'color' => 'text-emerald-600',
            'bg' => 'bg-emerald-100',
            'aspect' => 'thumb-doc',
            'is_required' => true,
        ],
        'Pas Foto' => [
            'key' => 'foto',
            'label' => 'Pas Foto 3x4',
            'icon' => 'fa-id-badge',
            'color' => 'text-amber-600',
            'bg' => 'bg-amber-100',
            'aspect' => 'thumb-pasfoto',
            'is_required' => true,
        ],
        'KPS' => [
            'key' => 'kps',
            'label' => 'Kartu KPS / KIP',
            'icon' => 'fa-id-card',
            'color' => 'text-purple-600',
            'bg' => 'bg-purple-100',
            'aspect' => 'thumb-doc',
            'is_required' => false,
        ],
    ];

    // Hitung kelengkapan dokumen wajib (3 dokumen wajib)
    $wajibVerifiedCount = 0;
    foreach (['Akta Kelahiran', 'Kartu Keluarga', 'Pas Foto'] as $reqDoc) {
        $found = $casis?->dokumen?->firstWhere('jenis_dokumen', $reqDoc);
        if ($found && $found->status_verifikasi === 'Valid') {
            $wajibVerifiedCount++;
        }
    }
@endphp

<div class="data-card-static overflow-hidden mb-6">
    {{-- Header Berkas --}}
    <div class="border-b border-slate-100 px-6 py-4 bg-slate-50/70 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="icon-box-fixed bg-purple-100 text-purple-700" aria-hidden="true">
                <i class="fas fa-folder-open text-base"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-base">Berkas Persyaratan</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Unggah scan dokumen fisik asli calon peserta didik baru.
                </p>
            </div>
        </div>

        {{-- Indikator Kelengkapan & Tombol Aksi --}}
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold rounded-full">
                <i class="fas fa-tasks text-slate-500"></i>
                <span>{{ $wajibVerifiedCount }} dari 3 dokumen wajib terverifikasi</span>
            </span>

            @if(!empty($isEditLocked))
                {{-- TODO: Penegakan kunci edit berkas di backend saat request POST daftarcasis --}}
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 text-slate-500 text-[13px] font-semibold rounded-lg border border-slate-200 cursor-not-allowed select-none">
                    <i class="fas fa-lock text-sm"></i>
                    <span>Terkunci</span>
                </span>
            @else
                <a href="{{ route('daftarcasis') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-colors shadow-xs min-h-[44px]"
                   title="Unggah / Edit Berkas">
                    <i class="fas fa-upload text-xs"></i>
                    <span>Unggah Berkas</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Grid 4 Kartu Dokumen (Responsive: 4 kol di >=1100px, 2 kol di 700-1100px, 1 kol di <700px) --}}
    <div class="p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($berkasList as $jenisDoc => $data)
                @php
                    $doc = $casis?->dokumen?->firstWhere('jenis_dokumen', $jenisDoc);
                    $hasDoc = !empty($doc?->file_path);
                    $fileUrl = $hasDoc ? route('dokumen.show', $doc->id_dokumen) : null;
                    $extension = $hasDoc ? strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION)) : '';
                    $statusDoc = $doc?->status_verifikasi ?? 'Belum Ada';
                    $isLegacyFormat = $hasDoc && $jenisDoc !== 'Pas Foto' && $extension !== 'pdf';

                    // Ukuran fisik file
                    $fileSizeFormatted = null;
                    if ($hasDoc) {
                        $fullDiskPath = storage_path('app/' . $doc->file_path);
                        if (file_exists($fullDiskPath)) {
                            $bytes = filesize($fullDiskPath);
                            if ($bytes >= 1048576) {
                                $fileSizeFormatted = number_format($bytes / 1048576, 2) . ' MB';
                            } else {
                                $fileSizeFormatted = number_format($bytes / 1024, 0) . ' KB';
                            }
                        }
                    }

                    // Format tanggal upload (Bahasa Indonesia)
                    $tglUpload = $doc?->tgl_upload
                        ? \Carbon\Carbon::parse($doc->tgl_upload)->locale('id')->translatedFormat('d M Y')
                        : null;
                @endphp

                <div class="border border-slate-200 rounded-xl p-4 flex flex-col justify-between bg-white hover:border-slate-300 transition-colors">
                    {{-- Judul & Label Dokumen --}}
                    <div>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="icon-box-fixed {{ $data['bg'] }} {{ $data['color'] }}" aria-hidden="true">
                                <i class="fas {{ $data['icon'] }} text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-bold text-slate-800 text-sm leading-snug truncate">{{ $data['label'] }}</h4>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-[11px] {{ $data['is_required'] ? 'text-slate-500 font-semibold' : 'text-purple-600 font-bold' }}">
                                        {{ $data['is_required'] ? 'Wajib' : 'Opsional' }}
                                    </span>
                                    @if($isLegacyFormat)
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            Format Lama
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Preview / Thumbnail / Icon Wadah --}}
                        <div class="mb-3">
                            @if($hasDoc)
                                @if($jenisDoc === 'Pas Foto')
                                    {{-- Pas Foto: Thumbnail potret 3:4 --}}
                                    <div class="h-36 w-28 mx-auto bg-slate-100 rounded-lg overflow-hidden border border-slate-200 relative group flex items-center justify-center shadow-xs">
                                        <img src="{{ $fileUrl }}" class="w-full h-full object-cover" alt="{{ $data['label'] }}">
                                    </div>
                                @elseif($extension === 'pdf')
                                    {{-- Dokumen PDF: Wadah ikon PDF tanpa img --}}
                                    <div class="h-36 bg-slate-50 border border-slate-200 rounded-lg p-3 flex flex-col items-center justify-center text-center">
                                        <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center mb-2" aria-hidden="true">
                                            <i class="fas fa-file-pdf text-2xl"></i>
                                        </div>
                                        <p class="text-xs font-bold text-slate-800 truncate w-full px-2" title="{{ $data['label'] }}.pdf">
                                            {{ $data['label'] }}.pdf
                                        </p>
                                        <span class="text-[11px] text-slate-500 font-medium mt-0.5">
                                            {{ $fileSizeFormatted ?? 'PDF' }}
                                        </span>
                                    </div>
                                @else
                                    {{-- Dokumen Format Lama (PNG/JPG dari migrasi awal) --}}
                                    <div class="h-36 bg-amber-50/40 border border-amber-200 rounded-lg p-3 flex flex-col items-center justify-center text-center">
                                        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 border border-amber-200 flex items-center justify-center mb-2" aria-hidden="true">
                                            <i class="fas fa-file-alt text-2xl"></i>
                                        </div>
                                        <p class="text-xs font-bold text-slate-800 truncate w-full px-2" title="{{ $data['label'] }}.{{ $extension }}">
                                            {{ $data['label'] }}.{{ $extension }}
                                        </p>
                                        <span class="text-[11px] text-slate-500 font-medium mt-0.5">
                                            {{ $fileSizeFormatted ?? strtoupper($extension) }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            Format Lama ({{ strtoupper($extension) }})
                                        </span>
                                    </div>
                                @endif
                            @else
                                <div class="h-36 bg-slate-50 border-2 border-dashed border-slate-200 rounded-lg flex flex-col items-center justify-center text-slate-400 p-3 text-center">
                                    <i class="fas fa-cloud-upload-alt text-2xl mb-1 opacity-40"></i>
                                    <span class="text-[11px] text-slate-400 font-medium">
                                        {{ $data['is_required'] ? 'Belum Diunggah' : 'Tidak Diunggah' }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $jenisDoc === 'Pas Foto' ? 'JPG/PNG, maks 2MB' : 'PDF, maks 5MB' }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Status Badge, Tanggal Unggah, Catatan Penolakan & Tombol --}}
                    <div class="space-y-2.5 pt-2 border-t border-slate-100">
                        {{-- Status Badge (Menunggu, Valid, Tidak Valid, Belum Ada) --}}
                        <div class="flex items-center justify-between gap-1 flex-wrap">
                            @if(!$hasDoc)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                    <i class="fas fa-minus-circle text-[11px]"></i>
                                    <span>{{ $data['is_required'] ? 'Belum Ada' : 'Tidak Ada' }}</span>
                                </span>
                            @elseif($statusDoc === 'Valid')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fas fa-check-circle text-[11px]"></i>
                                    <span>Valid</span>
                                </span>
                            @elseif($statusDoc === 'Tidak Valid')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    <i class="fas fa-times-circle text-[11px]"></i>
                                    <span>Tidak Valid</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fas fa-clock text-[11px]"></i>
                                    <span>Menunggu</span>
                                </span>
                            @endif

                            @if($tglUpload)
                                <span class="text-[11px] text-slate-500 font-medium" title="Tanggal unggah berkas">
                                    {{ $tglUpload }}
                                </span>
                            @endif
                        </div>

                        {{-- Catatan Penolakan bila ada --}}
                        @if($hasDoc && $statusDoc === 'Tidak Valid')
                            <div class="p-2 bg-rose-50 border border-rose-200 rounded-lg text-[11px] text-rose-700 leading-snug">
                                <strong>Alasan:</strong> {{ $doc->catatan ?? 'Berkas buram atau tidak terbaca. Harap unggah ulang.' }}
                            </div>
                        @endif

                        {{-- Tombol Aksi: Lihat atau Unggah Ulang --}}
                        <div class="pt-1 flex items-center gap-2">
                            @if($hasDoc)
                                <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition-colors min-h-[38px] border border-slate-200"
                                   aria-label="Lihat dokumen {{ $data['label'] }}">
                                    <i class="fas fa-eye text-xs"></i>
                                    <span>Lihat</span>
                                </a>
                            @endif

                            @if($statusDoc === 'Tidak Valid' && empty($isEditLocked))
                                <a href="{{ route('daftarcasis') }}"
                                   class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition-colors min-h-[38px] shadow-xs"
                                   aria-label="Unggah ulang dokumen {{ $data['label'] }}">
                                    <i class="fas fa-redo text-xs"></i>
                                    <span>Unggah Ulang</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
