@extends('layout.tampilanpanitia')

@section('page-title', 'Penilaian Seleksi')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 font-heading">
                Penilaian Seleksi
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Kelola penilaian hasil tes observasi (Membaca, Menulis, dan Berhitung) untuk calon siswa TA {{ $tahunAjarBerlangsung->tahun_ajaran ?? ($tahunAjarBerlangsung->tahun_ajar ?? '-') }}.
            </p>
        </div>

        {{-- Search --}}
        <form action="{{ route('nilai') }}" method="get" class="w-full md:w-auto">
            <div class="relative max-w-sm ml-auto">
                <input type="text"
                    name="search"
                    class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block pl-10 pr-4 py-2.5"
                    placeholder="Cari Nama atau No. Registrasi..."
                    value="{{ request('search') }}">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <i class="fas fa-search text-slate-400"></i>
                </div>
            </div>
        </form>
    </div>

    {{-- 4 Kartu Ringkasan Statistik --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Peserta Layak --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-users"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-500 font-medium">Peserta Layak</p>
                <p class="text-xl sm:text-2xl font-bold text-slate-800 mt-0.5">{{ $totalPeserta ?? 0 }}</p>
            </div>
        </div>

        {{-- Sudah Dinilai (Draft) --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-edit"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-500 font-medium">Sudah Dinilai</p>
                <p class="text-xl sm:text-2xl font-bold text-amber-600 mt-0.5">{{ $sudahDinilai ?? 0 }}</p>
            </div>
        </div>

        {{-- Belum Dinilai --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-500 font-medium">Belum Dinilai</p>
                <p class="text-xl sm:text-2xl font-bold {{ ($belumDinilai ?? 0) > 0 ? 'text-rose-600' : 'text-slate-800' }} mt-0.5">{{ $belumDinilai ?? 0 }}</p>
            </div>
        </div>

        {{-- Terkunci / Terpublikasi --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-lock"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-500 font-medium">Terpublikasi</p>
                <p class="text-xl sm:text-2xl font-bold text-emerald-600 mt-0.5">{{ $sudahDiumumkan ?? 0 }}</p>
            </div>
        </div>
    </div>

    {{-- Table --}}
    @include('components.panitia.table-nilai')

</div>
@endsection
