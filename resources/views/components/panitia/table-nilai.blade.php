<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-600">
            {{-- Head --}}
            <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-100">
                <tr>
                    <th class="px-5 py-4 font-semibold text-center w-12">No</th>
                    <th class="px-5 py-4 font-semibold">No. Registrasi</th>
                    <th class="px-5 py-4 font-semibold">Calon Siswa</th>
                    <th class="px-4 py-4 font-semibold text-center">Baca</th>
                    <th class="px-4 py-4 font-semibold text-center">Tulis</th>
                    <th class="px-4 py-4 font-semibold text-center">Hitung</th>
                    <th class="px-4 py-4 font-semibold text-center">Total</th>
                    <th class="px-5 py-4 font-semibold text-center">Hasil Otomatis</th>
                    <th class="px-5 py-4 font-semibold text-center">Status</th>
                    <th class="px-5 py-4 font-semibold text-center w-28">Aksi</th>
                </tr>
            </thead>

            {{-- Body --}}
            <tbody class="divide-y divide-slate-100">
                @forelse($data as $no => $value)
                @php
                    $seleksi = $value->pembayaran?->seleksi;
                    $isDiumumkan = $seleksi ? $seleksi->isDiumumkan() : false;
                    $isLulus = $seleksi && $seleksi->hasil_seleksi === 'Lulus';
                @endphp
                <tr class="hover:bg-slate-50/60 transition-colors {{ $isDiumumkan ? 'bg-slate-50/30' : '' }}">
                    {{-- No --}}
                    <td class="px-5 py-4 text-center font-medium text-slate-600">
                        {{ $no + 1 }}
                    </td>

                    {{-- No Registrasi --}}
                    <td class="px-5 py-4 font-mono font-semibold text-slate-700 text-xs sm:text-sm">
                        {{ $value->no_registrasi ?? '-' }}
                    </td>

                    {{-- Calon Siswa --}}
                    <td class="px-5 py-4">
                        <div class="font-bold text-slate-800">
                            {{ $value->nama_calon_siswa ?? $value->nama }}
                        </div>
                    </td>

                    {{-- Nilai Baca --}}
                    <td class="px-4 py-4 text-center font-semibold text-slate-700">
                        {{ $seleksi && $seleksi->nilai_baca !== null ? $seleksi->nilai_baca : '-' }}
                    </td>

                    {{-- Nilai Tulis --}}
                    <td class="px-4 py-4 text-center font-semibold text-slate-700">
                        {{ $seleksi && $seleksi->nilai_tulis !== null ? $seleksi->nilai_tulis : '-' }}
                    </td>

                    {{-- Nilai Hitung --}}
                    <td class="px-4 py-4 text-center font-semibold text-slate-700">
                        {{ $seleksi && $seleksi->nilai_hitung !== null ? $seleksi->nilai_hitung : '-' }}
                    </td>

                    {{-- Total Nilai --}}
                    <td class="px-4 py-4 text-center font-black text-blue-600">
                        {{ $seleksi && $seleksi->total_nilai !== null ? $seleksi->total_nilai : '-' }}
                    </td>

                    {{-- Hasil Seleksi Otomatis Server --}}
                    <td class="px-5 py-4 text-center">
                        @if($seleksi && $seleksi->total_nilai !== null)
                            @if($isLulus)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fas fa-check-circle text-[10px]"></i> LULUS
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    <i class="fas fa-times-circle text-[10px]"></i> TIDAK LULUS
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                <i class="fas fa-minus text-[10px]"></i> Belum Ada
                            </span>
                        @endif
                    </td>

                    {{-- Status Penilaian --}}
                    <td class="px-5 py-4 text-center">
                        @if($isDiumumkan)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"
                                  title="Diumumkan pada {{ $seleksi->diumumkan_pada?->translatedFormat('d M Y H:i') }}">
                                <i class="fas fa-lock text-[10px]"></i> Terpublikasi
                            </span>
                        @elseif($seleksi)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="fas fa-file-alt text-[10px]"></i> Draft Internal
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                <i class="fas fa-hourglass-start text-[10px]"></i> Belum Dinilai
                            </span>
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td class="px-5 py-4 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            @if($isDiumumkan)
                                {{-- Jika sudah diumumkan, hanya bisa melihat nilai yang terkunci --}}
                                <a href="{{ route('input', ['id' => $value->id_calon_siswa]) }}"
                                   class="px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold text-xs flex items-center gap-1 transition-colors"
                                   title="Nilai Terkunci (Sudah Diumumkan)">
                                    <i class="fas fa-eye text-xs"></i>
                                    <span>Lihat</span>
                                </a>
                            @else
                                {{-- Input / Edit Nilai --}}
                                <a href="{{ route('input', ['id' => $value->id_calon_siswa]) }}"
                                   class="px-2.5 py-1.5 rounded-lg {{ $seleksi ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }} font-semibold text-xs flex items-center gap-1 transition-colors"
                                   title="{{ $seleksi ? 'Edit Draft Nilai' : 'Input Nilai Seleksi' }}">
                                    <i class="fas {{ $seleksi ? 'fa-edit' : 'fa-plus' }} text-xs"></i>
                                    <span>{{ $seleksi ? 'Edit' : 'Nilai' }}</span>
                                </a>

                                {{-- Hapus Draft Nilai (Hanya jika belum diumumkan) --}}
                                @if($seleksi)
                                <form action="{{ route('hapus', ['id' => $value->id_calon_siswa]) }}"
                                      method="post"
                                      class="inline-block"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus draft nilai untuk siswa {{ addslashes($value->nama_calon_siswa) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition-colors"
                                            title="Hapus Draft Nilai">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </form>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300 mb-3 text-2xl">
                                <i class="fas fa-user-clock"></i>
                            </div>
                            <p class="text-slate-600 font-bold text-base">
                                Tidak ada peserta seleksi
                            </p>
                            <p class="text-slate-400 text-xs mt-1 max-w-sm">
                                Calon siswa akan otomatis masuk ke daftar ini setelah menyelesaikan pembayaran formulir pendaftaran.
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
