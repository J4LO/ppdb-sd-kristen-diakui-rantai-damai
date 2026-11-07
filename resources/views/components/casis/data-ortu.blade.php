@php
    $casis = $user->calonSiswa ?? $user->casis;
    
    // Normalisasi tautan WhatsApp jika nomor tersedia
    $rawHp = (string) ($casis?->no_hp_orang_tua ?? $casis?->no_hp ?? '');
    $cleanedHp = preg_replace('/[^0-9]/', '', $rawHp);
    if (str_starts_with($cleanedHp, '0')) {
        $cleanedHp = '62' . substr($cleanedHp, 1);
    }
    $waLink = $cleanedHp ? "https://wa.me/{$cleanedHp}" : null;
@endphp

<div class="data-card-static overflow-hidden flex flex-col h-full">
    {{-- Header Kartu (Satu Baris Seimbang) --}}
    <div class="border-b border-slate-100 px-6 py-4 bg-slate-50/70 flex items-center justify-between gap-4 min-h-[68px]">
        <div class="flex items-center gap-3">
            <div class="icon-box-fixed bg-amber-100 text-amber-700" aria-hidden="true">
                <i class="fas fa-user-friends text-lg"></i>
            </div>
            <h3 class="font-bold text-slate-900 text-lg leading-tight">Data Orang Tua / Wali</h3>
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
                   title="Ubah Data Orang Tua">
                    <i class="fas fa-pen text-xs"></i>
                    <span>Ubah</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Body Data Rapat 48-52px --}}
    <div class="p-6 flex-1 flex flex-col justify-between">
        <dl class="divide-y divide-slate-100">
            {{-- Data Ayah --}}
            <div class="data-row-item">
                <dt class="data-row-label">Nama Ayah</dt>
                <dd class="data-row-val">
                    {{ Str::title($casis?->nama_ayah ?? $casis?->nama_ortu ?? '-') }}
                </dd>
            </div>

            <div class="data-row-item">
                <dt class="data-row-label">Pendidikan Ayah</dt>
                <dd class="data-row-val">
                    {{ strtoupper($casis?->pendidikan_ayah ?? $casis?->pendidikan_ortu ?? '-') }}
                </dd>
            </div>

            <div class="data-row-item">
                <dt class="data-row-label">Pekerjaan Ayah</dt>
                <dd class="data-row-val">
                    {{ Str::title($casis?->pekerjaan_ayah ?? $casis?->pekerjaan_ortu ?? '-') }}
                </dd>
            </div>

            <div class="data-row-item">
                <dt class="data-row-label">Penghasilan Ayah</dt>
                <dd class="data-row-val">
                    {{ ($casis && ($casis->penghasilan_ayah || $casis->gaji_ortu)) ? 'Rp ' . number_format($casis->penghasilan_ayah ?: $casis->gaji_ortu, 0, ',', '.') : '-' }}
                </dd>
            </div>

            {{-- Data Ibu --}}
            <div class="data-row-item">
                <dt class="data-row-label">Nama Ibu</dt>
                <dd class="data-row-val">
                    {{ Str::title($casis?->nama_ibu ?? '-') }}
                </dd>
            </div>

            <div class="data-row-item">
                <dt class="data-row-label">Pendidikan Ibu</dt>
                <dd class="data-row-val">
                    {{ strtoupper($casis?->pendidikan_ibu ?? '-') }}
                </dd>
            </div>

            <div class="data-row-item">
                <dt class="data-row-label">Pekerjaan Ibu</dt>
                <dd class="data-row-val">
                    {{ Str::title($casis?->pekerjaan_ibu ?? '-') }}
                </dd>
            </div>

            <div class="data-row-item">
                <dt class="data-row-label">Penghasilan Ibu</dt>
                <dd class="data-row-val">
                    {{ ($casis && $casis->penghasilan_ibu) ? 'Rp ' . number_format($casis->penghasilan_ibu, 0, ',', '.') : '-' }}
                </dd>
            </div>

            {{-- Kontak --}}
            <div class="data-row-item">
                <dt class="data-row-label">No. HP / WhatsApp</dt>
                <dd class="data-row-val flex items-center gap-2">
                    <i class="fab fa-whatsapp text-emerald-600 text-base"></i>
                    @if($waLink)
                        <a href="{{ $waLink }}" target="_blank" class="text-emerald-700 hover:text-emerald-800 font-semibold hover:underline" title="Buka Chat WhatsApp">
                            {{ $rawHp }}
                        </a>
                    @else
                        <span>{{ $rawHp ?: '-' }}</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</div>
