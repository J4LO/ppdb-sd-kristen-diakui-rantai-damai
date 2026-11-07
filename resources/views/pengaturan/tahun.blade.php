@extends('layout.tampilan')

@section('page-title', 'Tahun Ajaran')

@section('content')
<div class="p-6 max-w-7xl mx-auto">

    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Pengaturan Tahun Ajaran</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola data tahun ajaran PPDB. Tahun ajaran dengan status <span class="font-semibold text-green-600">"Berlangsung"</span> akan menjadi TA Aktif di seluruh sistem.</p>
        </div>
        <a href="{{ route('tahun.add') }}" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold text-sm rounded-xl transition-all shadow-sm flex items-center gap-2 shrink-0">
            <i class="fas fa-plus"></i> Tambah Tahun Ajaran
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle"></i>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3">
            <i class="fas fa-exclamation-circle"></i>
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Active TA Banner --}}
    @php $berlangsung = $data->firstWhere('status', 'Berlangsung'); @endphp
    @if($berlangsung)
    <div class="mb-6 bg-gradient-to-r from-green-600 to-teal-600 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center shrink-0">
                <i class="fas fa-calendar-check text-white"></i>
            </div>
            <div>
                <p class="text-green-100 text-xs font-semibold uppercase tracking-wider">Tahun Ajaran Aktif Saat Ini</p>
                <p class="text-white text-2xl font-bold">{{ $berlangsung->tahun_ajar }}</p>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div class="bg-white/10 rounded-xl px-4 py-2">
                <p class="text-green-100 text-xs">Mulai Daftar</p>
                <p class="text-white font-bold text-sm">{{ \Carbon\Carbon::parse($berlangsung->mulai_pendaftaran)->format('d M Y') }}</p>
            </div>
            <div class="bg-white/10 rounded-xl px-4 py-2">
                <p class="text-green-100 text-xs">Batas Daftar</p>
                <p class="text-white font-bold text-sm">{{ \Carbon\Carbon::parse($berlangsung->batas_pendaftaran)->format('d M Y') }}</p>
            </div>
            <div class="bg-white/10 rounded-xl px-4 py-2">
                <p class="text-green-100 text-xs">Kuota</p>
                <p class="text-white font-bold text-sm">{{ $berlangsung->kuota }} Siswa</p>
            </div>
        </div>
    </div>
    @else
    <div class="mb-6 bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-lg shrink-0"></i>
        <p class="font-medium">Tidak ada Tahun Ajaran yang sedang berlangsung. Tambah atau ubah status TA menjadi <strong>"Berlangsung"</strong> agar sistem berjalan normal.</p>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-center text-sm border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase font-semibold tracking-wider">
                    <tr>
                        <th class="w-10 px-2.5 py-3.5 text-center">No</th>
                        <th class="px-2.5 py-3.5 text-center whitespace-nowrap">Tahun Ajaran</th>
                        <th class="px-2.5 py-3.5 text-center whitespace-nowrap">Mulai Daftar</th>
                        <th class="px-2.5 py-3.5 text-center whitespace-nowrap">Batas Daftar</th>
                        <th class="px-2.5 py-3.5 text-center whitespace-nowrap">Tgl Seleksi</th>
                        <th class="px-2.5 py-3.5 text-center whitespace-nowrap">Biaya Formulir</th>
                        <th class="px-2.5 py-3.5 text-center whitespace-nowrap">Biaya Daftar Ulang</th>
                        <th class="px-2.5 py-3.5 text-center">Kuota</th>
                        <th class="px-2.5 py-3.5 text-center">Status</th>
                        <th class="px-2.5 py-3.5 text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($data as $no => $value)
                    <tr class="hover:bg-slate-50/70 border-b border-slate-100 transition-colors align-middle {{ $value->status == 'Berlangsung' ? 'bg-green-50/30' : '' }}">
                        <td class="w-10 px-2.5 py-3.5 text-center font-medium text-slate-500 text-sm">{{ $no + 1 }}</td>
                        <td class="px-2.5 py-3.5 text-center whitespace-nowrap">
                            <span class="font-bold text-slate-800 text-sm">{{ $value->tahun_ajar }}</span>
                        </td>
                        <td class="px-2.5 py-3.5 text-slate-600 text-sm text-center whitespace-nowrap">{{ \Carbon\Carbon::parse($value->mulai_pendaftaran)->format('d M Y') }}</td>
                        <td class="px-2.5 py-3.5 text-slate-600 text-sm text-center whitespace-nowrap">{{ \Carbon\Carbon::parse($value->batas_pendaftaran)->format('d M Y') }}</td>
                        <td class="px-2.5 py-3.5 text-slate-600 text-sm text-center whitespace-nowrap">{{ \Carbon\Carbon::parse($value->tgl_seleksi)->format('d M Y') }}</td>
                        <td class="px-2.5 py-3.5 text-center font-medium text-slate-700 text-sm whitespace-nowrap">Rp {{ number_format($value->biaya_pendaftaran ?? 150000, 0, ',', '.') }}</td>
                        <td class="px-2.5 py-3.5 text-center whitespace-nowrap">
                            <span class="font-bold text-slate-800 text-sm">Rp {{ number_format($value->biaya_daftar_ulang ?? 1000000, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-2.5 py-3.5 text-center font-semibold text-slate-700 text-sm">{{ $value->kuota }}</td>
                        <td class="px-2.5 py-3.5 text-center whitespace-nowrap">
                            @if($value->status == 'Berlangsung')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse inline-block"></span> Berlangsung
                                </span>
                            @elseif($value->status == 'Belum Dimulai')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                                    <i class="fas fa-clock text-[9px]"></i> Belum Dimulai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                    <i class="fas fa-check text-[9px]"></i> Berakhir
                                </span>
                            @endif
                        </td>
                        <td class="px-2.5 py-3.5 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('tahun.edit', $value->id_ajar) }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-colors"
                                    title="Edit">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                <form action="{{ route('tahun.delete', $value->id_ajar) }}" method="POST"
                                    onsubmit="return confirm('Hapus Tahun Ajaran {{ $value->tahun_ajar }}?\nData yang sudah terhapus tidak dapat dipulihkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors"
                                        title="Hapus">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 text-slate-400 mb-4">
                                <i class="fas fa-calendar-times text-2xl"></i>
                            </div>
                            <h3 class="text-slate-800 font-bold text-lg mb-1">Belum ada data tahun ajaran</h3>
                            <p class="text-slate-500 text-sm mb-4">Tambahkan tahun ajaran pertama untuk memulai sistem PPDB.</p>
                            <a href="{{ route('tahun.add') }}" class="px-5 py-2 bg-green-600 text-white font-semibold text-sm rounded-xl hover:bg-green-700 transition-all">
                                <i class="fas fa-plus mr-1"></i> Tambah Sekarang
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
