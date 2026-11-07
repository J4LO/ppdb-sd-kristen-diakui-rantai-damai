@extends('layout.tampilan')

@section('page-title', 'Data Pengguna')

@push('styles')
<style>
    .password-toggle-btn {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        background: transparent;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        z-index: 10;
    }
    .password-toggle-btn:hover {
        color: #1e293b;
        background-color: #e2e8f0;
    }
    .password-toggle-btn:focus-visible {
        outline: 2px solid #16a34a;
        outline-offset: 1px;
    }
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear {
        display: none !important;
    }

    @media (min-width: 900px) {
        .user-desktop-view { display: block !important; }
        .user-mobile-view { display: none !important; }
    }
    @media (max-width: 899.98px) {
        .user-desktop-view { display: none !important; }
        .user-mobile-view { display: block !important; }
    }
</style>
@endpush

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="{
    openModal: false,
    selectedUser: null,
    newPassword: '',
    confirmPassword: '',
    showPass: false,
    showConfirm: false,
    get isMismatch() {
        return this.confirmPassword.length > 0 && this.newPassword !== this.confirmPassword;
    },
    openResetModal(user) {
        this.selectedUser = user;
        this.newPassword = '';
        this.confirmPassword = '';
        this.showPass = false;
        this.showConfirm = false;
        this.openModal = true;
    },
    closeResetModal() {
        this.openModal = false;
        this.selectedUser = null;
    }
}">

    {{-- 1. Header Section --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/90 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1 font-medium">
                <span>Master Data</span>
                <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
                <span class="text-emerald-700 font-semibold">Data Pengguna</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
                Data Akun Pengguna
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Kelola seluruh akun akses sistem PPDB (Administrator, Panitia PPDB, dan Orang Tua/Wali Calon Siswa).
            </p>
        </div>
    </div>

    {{-- 2. Flash Messages --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm font-medium shadow-xs">
            <i class="fas fa-check-circle text-emerald-600 text-base shrink-0"></i>
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm font-medium shadow-xs">
            <i class="fas fa-exclamation-circle text-rose-600 text-base shrink-0"></i>
            <p>{{ session('error') }}</p>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm shadow-xs space-y-1">
            <div class="flex items-center gap-2 font-semibold text-rose-900">
                <i class="fas fa-exclamation-triangle text-rose-600"></i>
                <span>Periksa kembali data Anda:</span>
            </div>
            <ul class="list-disc list-inside text-xs pl-5 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 3. Statistik Ringkasan Pengguna (4 Cards) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Users --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase font-bold tracking-wider text-slate-400">Total Pengguna</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $totalUsers ?? $data->count() }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Seluruh akun terdaftar</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-users"></i>
            </div>
        </div>

        {{-- Admin --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase font-bold tracking-wider text-indigo-500">Administrator</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-indigo-900 mt-1">{{ $totalAdmin ?? 0 }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Akses penuh sistem</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>

        {{-- Panitia --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase font-bold tracking-wider text-emerald-600">Panitia PPDB</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-emerald-900 mt-1">{{ $totalPanitia ?? 0 }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Petugas seleksi & pendaftaran</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-user-tie"></i>
            </div>
        </div>

        {{-- Orang Tua --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase font-bold tracking-wider text-blue-500">Orang Tua / Casis</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-blue-900 mt-1">{{ $totalOrtu ?? 0 }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Akun pendaftar siswa</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-user-friends"></i>
            </div>
        </div>
    </div>

    {{-- 4. Toolbar & Filter Form --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm">
        <form action="{{ route('beranda.datauser') }}" method="get" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            {{-- Pencarian --}}
            <div class="relative flex-1 min-w-0">
                <input type="text"
                       name="search"
                       value="{{ $search ?? request('search') }}"
                       placeholder="Cari nama, username, email, atau no. HP pengguna..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder:text-slate-400">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </div>
            </div>

            {{-- Filter Role --}}
            <div class="w-full md:w-56 relative">
                <select name="role"
                        class="w-full px-4 py-2.5 pr-8 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all appearance-none cursor-pointer">
                    <option value="">Semua Role Pengguna</option>
                    <option value="Admin" {{ ($role ?? request('role')) === 'Admin' ? 'selected' : '' }}>Administrator</option>
                    <option value="Panitia" {{ ($role ?? request('role')) === 'Panitia' ? 'selected' : '' }}>Panitia PPDB</option>
                    <option value="Orang Tua" {{ ($role ?? request('role')) === 'Orang Tua' ? 'selected' : '' }}>Orang Tua</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center gap-2">
                @if(!empty($search) || !empty($role))
                    <a href="{{ route('beranda.datauser') }}"
                       class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors inline-flex items-center gap-1.5"
                       title="Hapus Filter">
                        <i class="fas fa-undo-alt text-xs"></i>
                        <span>Reset</span>
                    </a>
                @endif
                <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-emerald-600/20 inline-flex items-center justify-center gap-2">
                    <i class="fas fa-filter text-xs"></i>
                    <span>Terapkan</span>
                </button>
            </div>
        </form>
    </div>

    {{-- 5. Tabel Data Pengguna (Desktop >= 900px) --}}
    <div class="user-desktop-view bg-white rounded-2xl shadow-sm border border-slate-200/90 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 text-xs uppercase font-bold tracking-wider">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 text-center w-12">NO</th>
                        <th scope="col" class="px-5 py-3.5 text-left min-w-[220px]">NAMA PENGGUNA</th>
                        <th scope="col" class="px-4 py-3.5 text-left min-w-[150px]">USERNAME</th>
                        <th scope="col" class="px-5 py-3.5 text-left min-w-[200px]">EMAIL</th>
                        <th scope="col" class="px-4 py-3.5 text-center min-w-[150px]">NO. HP</th>
                        <th scope="col" class="px-4 py-3.5 text-center">ROLE</th>
                        <th scope="col" class="px-4 py-3.5 text-center w-28">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($data as $no => $item)
                        @php
                            $nama = $item->nama ?? ($item->name ?? '-');
                            $words = explode(' ', trim($nama));
                            $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                            $hp = $item->no_hp ?? null;
                            $userRole = $item->role ?? 'Orang Tua';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            {{-- No --}}
                            <td class="px-4 py-3.5 text-center text-slate-500 font-medium text-sm">
                                {{ $loop->iteration }}
                            </td>

                            {{-- Nama & Profil --}}
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs shrink-0
                                        {{ $userRole === 'Admin' ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : ($userRole === 'Panitia' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-blue-100 text-blue-700 border border-blue-200') }}">
                                        {{ $initials ?: 'U' }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900 leading-snug">{{ $nama }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">ID User: #{{ $item->id_user }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Username --}}
                            <td class="px-4 py-3.5 text-slate-700 font-medium text-sm">
                                {{ $item->username ?: ($item->name ?: '-') }}
                            </td>

                            {{-- Email --}}
                            <td class="px-5 py-3.5 text-slate-600 text-sm">
                                <a href="mailto:{{ $item->email }}" class="hover:text-emerald-600 transition-colors">
                                    {{ $item->email }}
                                </a>
                            </td>

                            {{-- No. HP --}}
                            <td class="px-4 py-3.5 text-center text-slate-600 text-sm">
                                @if($hp)
                                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $hp)) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 text-slate-700 hover:text-emerald-600 font-medium transition-colors"
                                       title="Hubungi via WhatsApp">
                                        <i class="fab fa-whatsapp text-emerald-500 text-sm"></i>
                                        <span>{{ $hp }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- Role --}}
                            <td class="px-4 py-3.5 text-center">
                                @if($userRole === 'Admin')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i class="fas fa-user-shield text-[10px]"></i>
                                        <span>Administrator</span>
                                    </span>
                                @elseif($userRole === 'Panitia')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-user-tie text-[10px]"></i>
                                        <span>Panitia PPDB</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="fas fa-user-friends text-[10px]"></i>
                                        <span>Orang Tua</span>
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-3.5 text-center">
                                <button type="button"
                                        @click="openResetModal({{ json_encode(['id' => $item->id_user, 'nama' => $nama, 'email' => $item->email, 'role' => $userRole]) }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 rounded-xl text-xs font-semibold transition-all border border-slate-200"
                                        title="Ubah Password Pengguna Ini">
                                    <i class="fas fa-key text-[11px] text-amber-500"></i>
                                    <span>Reset</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl mb-3">
                                        <i class="fas fa-user-slash"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700">Tidak ada pengguna ditemukan</p>
                                    <p class="text-xs text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau filter role di atas.</p>
                                    <a href="{{ route('beranda.datauser') }}" class="mt-3 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl inline-flex items-center gap-1.5 transition-colors">
                                        <i class="fas fa-undo-alt text-xs"></i>
                                        <span>Reset Filter</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 6. Tampilan Kartu Pengguna (Mobile < 900px) --}}
    <div class="user-mobile-view space-y-3">
        @forelse($data as $no => $item)
            @php
                $nama = $item->nama ?? ($item->name ?? '-');
                $words = explode(' ', trim($nama));
                $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                $hp = $item->no_hp ?? null;
                $userRole = $item->role ?? 'Orang Tua';
            @endphp
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-sm space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shrink-0
                            {{ $userRole === 'Admin' ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : ($userRole === 'Panitia' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-blue-100 text-blue-700 border border-blue-200') }}">
                            {{ $initials ?: 'U' }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate-900 text-base truncate leading-snug">{{ $nama }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $item->email }}</p>
                        </div>
                    </div>

                    <div class="shrink-0">
                        @if($userRole === 'Admin')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                Admin
                            </span>
                        @elseif($userRole === 'Panitia')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Panitia
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                Orang Tua
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-100">
                    <div>
                        <span class="text-slate-400 block uppercase text-[10px]">Username</span>
                        <span class="font-semibold text-slate-800 mt-0.5 block truncate">
                            {{ $item->username ?: ($item->name ?: '-') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block uppercase text-[10px]">No. HP</span>
                        @if($hp)
                            <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $hp)) }}"
                               target="_blank"
                               class="font-semibold text-emerald-700 mt-0.5 inline-flex items-center gap-1">
                                <i class="fab fa-whatsapp text-emerald-500"></i>
                                <span>{{ $hp }}</span>
                            </a>
                        @else
                            <span class="text-slate-400 mt-0.5 block">-</span>
                        @endif
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex justify-end">
                    <button type="button"
                            @click="openResetModal({{ json_encode(['id' => $item->id_user, 'nama' => $nama, 'email' => $item->email, 'role' => $userRole]) }})"
                            class="w-full py-2 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 rounded-xl text-xs font-semibold transition-all inline-flex items-center justify-center gap-1.5 border border-slate-200">
                        <i class="fas fa-key text-[11px] text-amber-500"></i>
                        <span>Ubah Password Akun</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-6 text-center border border-slate-200/90 text-slate-500 text-sm">
                Tidak ada akun pengguna yang ditemukan.
            </div>
        @endforelse
    </div>

    {{-- 7. Modal Ubah / Reset Password Pengguna --}}
    <div x-show="openModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title"
         role="dialog"
         aria-modal="true"
         style="display: none;">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             x-show="openModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeResetModal()"></div>

        {{-- Modal Dialog --}}
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100 p-6 space-y-5"
                 x-show="openModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                {{-- Header Modal --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0 border border-amber-200">
                            <i class="fas fa-key"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 leading-snug" id="modal-title">
                                Reset Password Pengguna
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Atur ulang password untuk akun pengguna ini.
                            </p>
                        </div>
                    </div>
                    <button type="button"
                            @click="closeResetModal()"
                            class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                {{-- Detail Pengguna Target --}}
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between text-xs">
                    <div class="min-w-0">
                        <p class="font-bold text-slate-900 truncate" x-text="selectedUser ? selectedUser.nama : ''"></p>
                        <p class="text-slate-500 mt-0.5 truncate" x-text="selectedUser ? selectedUser.email : ''"></p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full font-bold uppercase text-[10px] bg-white border border-slate-200 text-slate-700 shrink-0"
                          x-text="selectedUser ? selectedUser.role : ''"></span>
                </div>

                {{-- Form Reset Password --}}
                <form :action="selectedUser ? ('/admin/data/user/' + selectedUser.id + '/reset-password') : '#'"
                      method="post"
                      class="space-y-4">
                    @csrf

                    {{-- Password Baru --}}
                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-slate-700 block">Password Baru <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'"
                                   name="password"
                                   x-model="newPassword"
                                   required
                                   minlength="6"
                                   placeholder="Minimal 6 karakter..."
                                   class="w-full px-4 py-2.5 pr-11 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                            <button type="button"
                                    @click="showPass = !showPass"
                                    class="password-toggle-btn"
                                    tabindex="-1"
                                    title="Tampilkan / Sembunyikan Password">
                                <i class="fas text-sm" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-slate-700 block">Konfirmasi Password Baru <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input :type="showConfirm ? 'text' : 'password'"
                                   name="password_confirmation"
                                   x-model="confirmPassword"
                                   required
                                   minlength="6"
                                   placeholder="Ulangi password baru..."
                                   :class="isMismatch ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20'"
                                   class="w-full px-4 py-2.5 pr-11 bg-slate-50 border rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 transition-all">
                            <button type="button"
                                    @click="showConfirm = !showConfirm"
                                    class="password-toggle-btn"
                                    tabindex="-1"
                                    title="Tampilkan / Sembunyikan Password">
                                <i class="fas text-sm" :class="showConfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        <p x-show="isMismatch" class="text-xs text-rose-500 font-medium flex items-center gap-1">
                            <i class="fas fa-exclamation-circle text-[11px]"></i> Konfirmasi password tidak cocok.
                        </p>
                    </div>

                    {{-- Tombol Aksi Modal --}}
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button"
                                @click="closeResetModal()"
                                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="isMismatch || newPassword.length < 6"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-emerald-600/20 inline-flex items-center gap-2">
                            <i class="fas fa-check"></i>
                            <span>Simpan Password</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
