@extends('layout.tampilan')

@section('page-title', 'Data Panitia')

@section('content')
<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Data Panitia</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola data seluruh kepanitiaan PPDB.</p>
        </div>
        <a href="/admin/data/panitia/add" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 text-white hover:bg-green-700 rounded-xl text-sm font-medium transition-all shadow-sm shadow-green-600/20 active:scale-[0.98]">
            <i class="fas fa-plus"></i> Tambah Panitia
        </a>
    </div>

    {{-- Main Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        {{-- Toolbar --}}
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row justify-between gap-4">
            <form action="/admin/data/panitia" method="get" class="w-full sm:max-w-md relative group">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-green-500 transition-colors"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama atau email panitia..." class="w-full pl-10 pr-4 py-2 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all placeholder:text-slate-400">
                <button type="submit" class="hidden"></button>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-medium">
                    <tr>
                        <th class="px-5 py-4">Nama Lengkap</th>
                        <th class="px-5 py-4">Username</th>
                        <th class="px-5 py-4">Email</th>
                        <th class="px-5 py-4">No. HP</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($data as $value)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($value->nama, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $value->nama }}</p>
                                    <p class="text-xs text-slate-500 hidden sm:block">ID: {{ $value->id_panitia }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-slate-600 font-medium">{{ $value->user->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-slate-500">{{ $value->user->email ?? '-' }}</td>
                        <td class="px-5 py-4 text-slate-600">
                            @php
                                $hp = $value->no_hp ?: ($value->user->no_hp ?? null);
                            @endphp
                            @if($hp)
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $hp)) }}" target="_blank" class="inline-flex items-center gap-1.5 text-slate-700 hover:text-green-600 font-medium transition-colors" title="Hubungi via WhatsApp">
                                    <i class="fab fa-whatsapp text-emerald-500 text-sm"></i>
                                    <span>{{ $hp }}</span>
                                </a>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($value->status == 'Aktif')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Non-Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="/admin/data/panitia/edit/{{$value->id_panitia}}" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('deletepanitia', $value->id_panitia ?? $value->user->id_user) }}" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus panitia {{ $value->nama }}? Data tidak dapat dikembalikan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 text-slate-400 mb-3">
                                <i class="fas fa-users-slash text-xl"></i>
                            </div>
                            <h3 class="text-slate-800 font-medium">Tidak ada data</h3>
                            <p class="text-slate-500 text-sm mt-1">Belum ada data panitia yang ditambahkan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if(method_exists($data, 'links') && $data->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $data->links() }}
            </div>
        @endif
    </div>
</div>
@endsection