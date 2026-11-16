@extends('layout.tampilan')

@section('page-title', 'Biaya Daftar Ulang')

@section('content')
<div class="p-6 max-w-7xl mx-auto">

    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Biaya Daftar Ulang</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola rincian biaya daftar ulang untuk Tahun Ajaran aktif saat ini.</p>
        </div>
        <button type="button" data-toggle="modal" data-target="#addModal" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl transition-all shadow-sm flex items-center gap-2 shrink-0">
            <i class="fas fa-plus"></i> Tambah Biaya
        </button>
    </div>

    <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-5 py-4 rounded-xl flex items-center gap-3">
        <i class="fas fa-info-circle text-xl shrink-0"></i>
        <div>
            <p class="font-bold text-sm uppercase tracking-wide text-blue-700 mb-0.5">Tahun Ajaran Aktif</p>
            <p class="text-lg font-extrabold">{{ $tahunAjarAktif->tahun_ajar }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500">
                    <tr>
                        <th class="px-6 py-4 font-semibold">No</th>
                        <th class="px-6 py-4 font-semibold">Nama Biaya</th>
                        <th class="px-6 py-4 font-semibold">Nominal</th>
                        <th class="px-6 py-4 font-semibold text-center">Ditujukan Untuk</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($biayaDaftarUlang as $index => $biaya)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 text-slate-500">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-semibold text-slate-800">{{ $biaya->nama_biaya }}</td>
                        <td class="px-6 py-4 font-bold text-slate-700">Rp {{ number_format($biaya->nominal, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($biaya->jenis_kelamin == 'Semua')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Semua Siswa</span>
                            @elseif($biaya->jenis_kelamin == 'Laki-Laki')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fas fa-mars mr-1"></i> Laki-Laki</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800"><i class="fas fa-venus mr-1"></i> Perempuan</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" data-toggle="modal" data-target="#editModal{{ $biaya->id }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-colors"
                                    title="Edit">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <form action="{{ route('biaya-daftar-ulang.destroy', $biaya->id) }}" method="POST"
                                    onsubmit="return confirm('Hapus Biaya {{ $biaya->nama_biaya }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors"
                                        title="Hapus">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 text-slate-400 mb-4">
                                <i class="fas fa-file-invoice-dollar text-2xl"></i>
                            </div>
                            <h3 class="text-slate-800 font-bold text-lg mb-1">Belum ada rincian biaya</h3>
                            <p class="text-slate-500 text-sm mb-4">Tambahkan rincian biaya daftar ulang untuk tahun ajaran ini.</p>
                            <button type="button" data-toggle="modal" data-target="#addModal" class="px-5 py-2 bg-blue-600 text-white font-semibold text-sm rounded-xl hover:bg-blue-700 transition-all">
                                <i class="fas fa-plus mr-1"></i> Tambah Biaya
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah (Tailwind Styled within Bootstrap Modal Structure) -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-2xl border-0 shadow-lg">
            <div class="modal-header border-b border-slate-100 bg-slate-50 rounded-t-2xl px-6 py-4">
                <h5 class="modal-title font-bold text-slate-800">Tambah Biaya Daftar Ulang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('biaya-daftar-ulang.store') }}" method="POST">
                @csrf
                <div class="modal-body p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Biaya <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_biaya" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" required placeholder="Contoh: Seragam Olahraga">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nominal (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="nominal" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" required placeholder="Contoh: 150000">
                    </div>
                    <div class="hidden">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-500 cursor-not-allowed" value="{{ $tahunAjarAktif->tahun_ajar }}" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Ditujukan Untuk <span class="text-red-500">*</span></label>
                        <select name="jenis_kelamin" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" required>
                            <option value="Semua">Semua Siswa</option>
                            <option value="Laki-Laki">Hanya Siswa Laki-Laki</option>
                            <option value="Perempuan">Hanya Siswa Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-100 bg-slate-50 rounded-b-2xl px-6 py-4">
                    <button type="button" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors" data-dismiss="modal">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-sm transition-colors">Simpan Biaya</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
@foreach($biayaDaftarUlang as $biaya)
<div class="modal fade" id="editModal{{ $biaya->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-2xl border-0 shadow-lg">
            <div class="modal-header border-b border-slate-100 bg-slate-50 rounded-t-2xl px-6 py-4">
                <h5 class="modal-title font-bold text-slate-800">Edit Biaya Daftar Ulang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('biaya-daftar-ulang.update', $biaya->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Biaya <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_biaya" value="{{ $biaya->nama_biaya }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-slate-700 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nominal (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="nominal" value="{{ $biaya->nominal }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-slate-700 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" required>
                    </div>
                    <div class="hidden">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-500 cursor-not-allowed" value="{{ $tahunAjarAktif->tahun_ajar }}" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Ditujukan Untuk <span class="text-red-500">*</span></label>
                        <select name="jenis_kelamin" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-slate-700 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" required>
                            <option value="Semua" {{ $biaya->jenis_kelamin == 'Semua' ? 'selected' : '' }}>Semua Siswa</option>
                            <option value="Laki-Laki" {{ $biaya->jenis_kelamin == 'Laki-Laki' ? 'selected' : '' }}>Hanya Siswa Laki-Laki</option>
                            <option value="Perempuan" {{ $biaya->jenis_kelamin == 'Perempuan' ? 'selected' : '' }}>Hanya Siswa Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-100 bg-slate-50 rounded-b-2xl px-6 py-4">
                    <button type="button" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors" data-dismiss="modal">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl shadow-sm transition-colors">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
