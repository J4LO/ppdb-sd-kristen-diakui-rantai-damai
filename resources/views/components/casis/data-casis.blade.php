@php
    $casis = $user->calonSiswa ?? $user->casis;
    $rawNik = (string) ($casis?->nik_calon_siswa ?? $casis?->nik ?? '');
    $maskedNik = strlen($rawNik) > 8
        ? substr($rawNik, 0, 4) . '••••••••' . substr($rawNik, -4)
        : ($rawNik ?: '-');

    $tglLahirFormat = ($casis && $casis->tgl_lahir)
        ? \Carbon\Carbon::parse($casis->tgl_lahir)->locale('id')->translatedFormat('j M Y')
        : '-';
@endphp

<div class="data-card-static overflow-hidden flex flex-col h-full" x-data="{ showNik: false }">
    {{-- Header Kartu (Satu Baris Seimbang) --}}
    <div class="border-b border-slate-100 px-6 py-4 bg-slate-50/70 flex items-center justify-between gap-4 min-h-[68px]">
        <div class="flex items-center gap-3">
            <div class="icon-box-fixed bg-blue-100 text-blue-700" aria-hidden="true">
                <i class="fas fa-child text-lg"></i>
            </div>
            <h3 class="font-bold text-slate-900 text-lg leading-tight">Data Calon Siswa</h3>
        </div>

        {{-- Penanda Terkunci / Tombol Ubah --}}
        <div>
            @if(!empty($isEditLocked))
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 text-slate-500 text-[13px] font-semibold rounded-lg border border-slate-200 cursor-not-allowed select-none">
                    <i class="fas fa-lock text-sm"></i>
                    <span>Terkunci</span>
                </span>
            @else
                <a href="{{ route('daftarcasis') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl transition-colors shadow-xs min-h-[44px]"
                   title="Ubah Data Calon Siswa">
                    <i class="fas fa-pen text-xs"></i>
                    <span>Ubah</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Body Data Rapat 48-52px --}}
    <div class="p-6 flex-1 flex flex-col justify-between">
        <dl class="divide-y divide-slate-100">
            {{-- NIK --}}
            <div class="data-row-item">
                <dt class="data-row-label">NIK</dt>
                <dd class="data-row-val flex items-center gap-3">
                    <span class="font-mono text-[15px] sm:text-base font-semibold tracking-normal whitespace-nowrap shrink-0" x-text="showNik ? '{{ $rawNik }}' : '{{ $maskedNik }}'">
                        {{ $maskedNik }}
                    </span>
                    @if(strlen($rawNik) > 0)
                        <button type="button" @click="showNik = !showNik"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-blue-700 hover:bg-blue-50 bg-slate-50 border border-slate-200 rounded-lg transition-colors focus-visible:outline-2 focus-visible:outline-blue-600 shrink-0 min-h-[32px]"
                                :title="showNik ? 'Sembunyikan NIK' : 'Tampilkan NIK'"
                                :aria-pressed="showNik.toString()"
                                :aria-label="showNik ? 'Sembunyikan NIK' : 'Tampilkan NIK'">
                            <i class="fas text-xs" :class="showNik ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
                            <span x-text="showNik ? 'Sembunyikan' : 'Tampilkan'">Tampilkan</span>
                        </button>
                    @endif
                </dd>
            </div>

            {{-- Nama Lengkap --}}
            <div class="data-row-item">
                <dt class="data-row-label">Nama Lengkap</dt>
                <dd class="data-row-val">
                    {{ Str::title($casis?->nama_calon_siswa ?? $casis?->nama ?? '-') }}
                </dd>
            </div>

            {{-- Tempat, Tgl Lahir --}}
            <div class="data-row-item">
                <dt class="data-row-label">Tempat, Tgl Lahir</dt>
                <dd class="data-row-val">
                    {{ Str::title($casis?->tmpt_lahir ?? $casis?->tempat_lahir ?? '-') }}, {{ $tglLahirFormat }}
                </dd>
            </div>

            {{-- Jenis Kelamin --}}
            <div class="data-row-item">
                <dt class="data-row-label">Jenis Kelamin</dt>
                <dd class="data-row-val">
                    {{ $casis?->jenis_kelamin ?? '-' }}
                </dd>
            </div>

            {{-- Agama --}}
            <div class="data-row-item">
                <dt class="data-row-label">Agama</dt>
                <dd class="data-row-val">
                    {{ $casis?->agama ?? '-' }}
                </dd>
            </div>

            {{-- Status dalam Keluarga --}}
            <div class="data-row-item">
                <dt class="data-row-label">Status dalam Keluarga</dt>
                <dd class="data-row-val">
                    {{ Str::title($casis?->status_anak ?? '-') }}
                </dd>
            </div>

            {{-- Jumlah Saudara --}}
            <div class="data-row-item">
                <dt class="data-row-label">Jumlah Saudara</dt>
                <dd class="data-row-val">
                    {{ $casis?->jmlh_saudara ?? $casis?->jml_saudara ?? 0 }}
                </dd>
            </div>

            {{-- Alamat Lengkap --}}
            <div class="data-row-item">
                <dt class="data-row-label">Alamat Lengkap</dt>
                <dd class="data-row-val text-slate-700 font-medium">
                    {{ $casis?->alamat_calon_siswa ?? $casis?->alamat ?? '-' }}
                </dd>
            </div>
        </dl>
    </div>
</div>
