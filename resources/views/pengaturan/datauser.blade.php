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

    @media (min-width: 1200px) {
        .user-desktop-view { display: block !important; }
        .user-mobile-view { display: none !important; }
    }
    @media (max-width: 1199.98px) {
        .user-desktop-view { display: none !important; }
        .user-mobile-view { display: block !important; }
    }
</style>
@endpush

@section('content')
<div class="datauser-page space-y-6 max-w-7xl mx-auto" x-data="{
    // Modal 1: Reset Password
    openModal: false,
    selectedUser: null,
    newPassword: '',
    confirmPassword: '',
    showPass: false,
    showConfirm: false,
    lastTriggerBtn: null,
    get isMismatch() {
        return this.confirmPassword.length > 0 && this.newPassword !== this.confirmPassword;
    },
    openResetModal(user) {
        this.closeMenu();
        this.selectedUser = user;
        this.newPassword = '';
        this.confirmPassword = '';
        this.showPass = false;
        this.showConfirm = false;
        this.openModal = true;
        document.body.classList.add('overflow-hidden');
    },
    closeResetModal() {
        this.openModal = false;
        this.selectedUser = null;
        document.body.classList.remove('overflow-hidden');
    },

    // Floating Popover Menu (Fixed Position - Bebas dari pemotongan overflow tabel)
    activeMenu: null,
    openMenu(event, menuData) {
        const btn = event.currentTarget;
        this.lastTriggerBtn = btn;
        const rect = btn.getBoundingClientRect();
        const menuWidth = 200;
        const menuHeight = menuData.canDelete ? 96 : 48;
        const spaceBelow = window.innerHeight - rect.bottom;
        const openUp = spaceBelow < (menuHeight + 20) && rect.top > menuHeight;

        let left = rect.right - menuWidth;
        if (left < 12) left = 12;
        if (left + menuWidth > window.innerWidth - 12) {
            left = window.innerWidth - menuWidth - 12;
        }

        let top = openUp ? (rect.top - menuHeight - 6) : (rect.bottom + 6);

        this.activeMenu = {
            ...menuData,
            btnEl: btn,
            top: Math.round(top),
            left: Math.round(left),
            openUp
        };

        this.$nextTick(() => {
            const firstItem = document.querySelector('#floating-user-menu [role=\'menuitem\']');
            if (firstItem) firstItem.focus();
        });
    },
    closeMenu() {
        if (this.activeMenu && this.activeMenu.btnEl) {
            this.lastTriggerBtn = this.activeMenu.btnEl;
        }
        this.activeMenu = null;
    },
    focusNextMenuItem(direction) {
        const items = Array.from(document.querySelectorAll('#floating-user-menu [role=\'menuitem\']'));
        if (!items.length) return;
        const curIdx = items.indexOf(document.activeElement);
        let nextIdx = curIdx + direction;
        if (nextIdx < 0) nextIdx = items.length - 1;
        if (nextIdx >= items.length) nextIdx = 0;
        items[nextIdx].focus();
    },

    // Modal 2: Nonaktifkan Akun
    modalNonaktif: false,
    modeNonaktif: 'nonaktifkan',
    openNonaktifModal(user, mode = 'nonaktifkan') {
        this.selectedUser = user;
        this.modeNonaktif = mode;
        this.modalNonaktif = true;
        this.closeMenu();
        document.body.classList.add('overflow-hidden');
        this.$nextTick(() => {
            if (this.$refs.btnBatalNonaktif) this.$refs.btnBatalNonaktif.focus();
        });
    },
    closeNonaktifModal() {
        this.modalNonaktif = false;
        document.body.classList.remove('overflow-hidden');
        if (this.lastTriggerBtn) {
            this.lastTriggerBtn.focus();
        }
    },

    // Modal 3: Hapus Permanen
    modalHapus: false,
    inputConfirmUsername: '',
    get confirmTarget() {
        return (this.selectedUser?.username || this.selectedUser?.nama || '').trim();
    },
    get isHapusValid() {
        return this.confirmTarget.length > 0 && this.inputConfirmUsername.trim() === this.confirmTarget;
    },
    openHapusModal(user) {
        this.selectedUser = user;
        this.inputConfirmUsername = '';
        this.modalHapus = true;
        this.closeMenu();
        document.body.classList.add('overflow-hidden');
        this.$nextTick(() => {
            if (this.$refs.btnBatalHapus) this.$refs.btnBatalHapus.focus();
        });
    },
    closeHapusModal() {
        this.modalHapus = false;
        this.inputConfirmUsername = '';
        document.body.classList.remove('overflow-hidden');
        if (this.lastTriggerBtn) {
            this.lastTriggerBtn.focus();
        }
    },

    triggerNonaktifkan() {
        if (!this.activeMenu) return;
        const u = {
            id: this.activeMenu.id,
            nama: this.activeMenu.nama,
            username: this.activeMenu.username,
            email: this.activeMenu.email,
            role: this.activeMenu.role
        };
        this.openNonaktifModal(u, 'nonaktifkan');
    },

    triggerHapusPermanen() {
        if (!this.activeMenu) return;
        const u = {
            id: this.activeMenu.id,
            nama: this.activeMenu.nama,
            username: this.activeMenu.username,
            email: this.activeMenu.email,
            role: this.activeMenu.role
        };
        this.openHapusModal(u);
    },

    trapFocus(e, modalRef) {
        if (!modalRef) return;
        const focusable = modalRef.querySelectorAll('button:not([disabled]), input:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex=\'-1\'])');
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    }
}"
@keydown.escape.window="if (activeMenu) closeMenu(); if (modalNonaktif) closeNonaktifModal(); if (modalHapus) closeHapusModal(); if (openModal) closeResetModal();">

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
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm font-medium shadow-xs" role="alert" aria-live="polite">
            <i class="fas fa-check-circle text-emerald-600 text-base shrink-0"></i>
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm font-medium shadow-xs" role="alert" aria-live="assertive">
            <i class="fas fa-exclamation-circle text-rose-600 text-base shrink-0"></i>
            <p>{{ session('error') }}</p>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm shadow-xs space-y-1" role="alert" aria-live="assertive">
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

    {{-- 3. Statistik Ringkasan Pengguna (4 Cards Terpasang + TODO Kartu Nonaktif) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- Total Users (Menghitung seluruh akun) --}}
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

        {{-- TODO: Kartu ringkasan 'Nonaktif' (jumlah akun nonaktif semua role) membutuhkan kolom is_active/status di tb_users dan query baru $totalNonaktif di PengaturanController@datauser. Saat ini disembunyikan sampai kolom database dan controller query diterapkan. --}}
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
                    {{-- TODO: Pilihan filter 'Nonaktif' membutuhkan penambahan kolom status/is_active di tb_users dan dukungan parameter di PengaturanController@datauser. Dinonaktifkan sementara sampai didukung backend. --}}
                    <option value="Nonaktif" disabled class="text-slate-400">Nonaktif (TODO: Butuh Backend)</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center gap-2">
                @if(!empty($search) || !empty($role))
                    <a href="{{ route('beranda.datauser') }}"
                       class="min-h-[44px] px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors inline-flex items-center gap-1.5 focus-visible:outline-2 focus-visible:outline-emerald-600"
                       title="Hapus Filter">
                        <i class="fas fa-undo-alt text-xs"></i>
                        <span>Reset</span>
                    </a>
                @endif
                <button type="submit"
                        class="min-h-[44px] px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-emerald-600/20 inline-flex items-center justify-center gap-2 focus-visible:outline-2 focus-visible:outline-emerald-600">
                    <i class="fas fa-filter text-xs"></i>
                    <span>Terapkan</span>
                </button>
            </div>
        </form>
    </div>

    {{-- 5. Tabel Data Pengguna (Desktop >= 1200px) --}}
    <div class="user-desktop-view bg-white rounded-2xl shadow-sm border border-slate-200/90 overflow-hidden">
        <div class="overflow-x-auto xl:overflow-x-clip">
            <table class="w-full text-left text-[13px] border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 text-xs uppercase font-bold tracking-wider">
                    <tr>
                        <th scope="col" class="px-2 py-3 text-center w-8">NO</th>
                        <th scope="col" class="px-3 py-3 text-left">NAMA PENGGUNA</th>
                        <th scope="col" class="px-2.5 py-3 text-left">USERNAME</th>
                        <th scope="col" class="px-3 py-3 text-left">EMAIL</th>
                        <th scope="col" class="px-2.5 py-3 text-center">NO. HP</th>
                        <th scope="col" class="px-2.5 py-3 text-center">ROLE</th>
                        <th scope="col" class="px-2.5 py-3 text-center">STATUS</th>
                        <th scope="col" class="px-2.5 py-3 text-center">TERAKHIR LOGIN</th>
                        <th scope="col" class="px-2.5 py-3 text-center">AKSI</th>
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

                            // Pengecekan kondisi akun
                            $isSelf = auth()->check() && (auth()->id() == $item->id_user);
                            $isAdminRole = ($userRole === 'Admin');
                            $isOnlyAdmin = $isAdminRole && (($totalAdmin ?? 1) <= 1);

                            // Status akun: default true bila kolom database belum ada
                            $isActiveAccount = true;
                            if (isset($item->is_active)) {
                                $isActiveAccount = (bool) $item->is_active;
                            } elseif (isset($item->status)) {
                                $isActiveAccount = in_array(strtolower($item->status), ['aktif', 'active', '1', 1]);
                            }

                            // Relasi calon siswa aktif (khusus Orang Tua)
                            $hasActiveCasis = false;
                            if ($userRole === 'Orang Tua') {
                                $hasActiveCasis = $item->calonSiswa()->exists();
                            }

                            // Hak opsi aksi tambahan (menu titik tiga)
                            $canDeactivate = false;
                            $canDelete = false;

                            if (!$isSelf && !$isOnlyAdmin) {
                                if ($isActiveAccount) {
                                    $canDeactivate = true;
                                    // Hapus permanen hanya untuk Orang Tua tanpa calon siswa aktif
                                    if ($userRole === 'Orang Tua' && !$hasActiveCasis) {
                                        $canDelete = true;
                                    }
                                } else {
                                    $canDelete = true;
                                }
                            }

                            $hasMoreActions = $canDeactivate || $canDelete;

                            $menuPayload = [
                                'id' => $item->id_user,
                                'nama' => $nama,
                                'username' => $item->username ?: ($item->nama ?: ''),
                                'email' => $item->email,
                                'role' => $userRole,
                                'canDeactivate' => $canDeactivate,
                                'canDelete' => $canDelete,
                                'isActive' => $isActiveAccount
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            {{-- 1. No --}}
                            <td class="px-2 py-3 text-center text-slate-500 font-medium">
                                {{ $loop->iteration }}
                            </td>

                            {{-- 2. Nama Pengguna & ID --}}
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0
                                        {{ $userRole === 'Admin' ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : ($userRole === 'Panitia' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-blue-100 text-blue-700 border border-blue-200') }}">
                                        {{ $initials ?: 'U' }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <p class="font-semibold text-slate-900 leading-snug truncate">{{ $nama }}</p>
                                            @if($isSelf)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 shrink-0">Anda</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-400 mt-0.5">ID: #{{ $item->id_user }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- 3. Username --}}
                            <td class="px-2.5 py-3 text-slate-700 font-medium">
                                {{ $item->username ?: ($item->name ?: '-') }}
                            </td>

                            {{-- 4. Email --}}
                            <td class="px-3 py-3 text-slate-600">
                                <a href="mailto:{{ $item->email }}" class="hover:text-emerald-600 transition-colors">
                                    {{ $item->email }}
                                </a>
                            </td>

                            {{-- 5. No. HP --}}
                            <td class="px-2.5 py-3 text-center text-slate-600">
                                @if($hp)
                                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $hp)) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 text-slate-700 hover:text-emerald-600 font-medium transition-colors"
                                       title="Hubungi via WhatsApp">
                                        <i class="fab fa-whatsapp text-emerald-500 text-xs"></i>
                                        <span>{{ $hp }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- 6. Role --}}
                            <td class="px-2.5 py-3 text-center">
                                @if($userRole === 'Admin')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i class="fas fa-user-shield text-[10px]"></i>
                                        <span>Administrator</span>
                                    </span>
                                @elseif($userRole === 'Panitia')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-user-tie text-[10px]"></i>
                                        <span>Panitia PPDB</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="fas fa-user-friends text-[10px]"></i>
                                        <span>Orang Tua</span>
                                    </span>
                                @endif
                            </td>

                            {{-- 7. Status Akun --}}
                            <td class="px-2.5 py-3 text-center">
                                @if(isset($item->is_active) || isset($item->status))
                                    @if($isActiveAccount)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Aktif</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            <span>Nonaktif</span>
                                        </span>
                                    @endif
                                @else
                                    {{-- TODO: Kolom is_active/status belum tersedia di tb_users dan controller datauser. Menampilkan badge default Aktif. --}}
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Akun aktif (default sistem)">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Aktif</span>
                                    </span>
                                @endif
                            </td>

                            {{-- 8. Terakhir Login --}}
                            <td class="px-2.5 py-3 text-center text-xs">
                                @if(!empty($item->last_login_at) || !empty($item->last_seen_at))
                                    @php
                                        $lastLogin = $item->last_login_at ?? $item->last_seen_at;
                                        $formattedDate = \Carbon\Carbon::parse($lastLogin)->timezone('Asia/Makassar')->translatedFormat('d M Y, H.i') . ' WITA';
                                    @endphp
                                    <span class="text-slate-700 font-medium whitespace-nowrap">{{ $formattedDate }}</span>
                                @else
                                    {{-- TODO: Kolom last_login_at atau last_seen_at belum tersedia di tb_users. Menampilkan teks default 'Belum pernah login'. --}}
                                    <span class="text-slate-400 font-normal">Belum pernah login</span>
                                @endif
                            </td>

                            {{-- 9. Aksi --}}
                            <td class="px-2.5 py-3 text-center">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    {{-- Tombol Utama: Reset Password (atau Aktifkan bila akun nonaktif) --}}
                                    @if(!$isActiveAccount)
                                        <button type="button"
                                                @click="openNonaktifModal({{ json_encode($menuPayload) }}, 'aktifkan')"
                                                class="min-h-[44px] inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-xl text-xs font-semibold transition-all border border-emerald-200 focus-visible:outline-2 focus-visible:outline-emerald-600"
                                                title="Aktifkan Kembali Akun Pengguna">
                                            <i class="fas fa-check-circle text-[11px] text-emerald-600"></i>
                                            <span>Aktifkan</span>
                                        </button>
                                    @else
                                        <button type="button"
                                                @click="openResetModal({{ json_encode(['id' => $item->id_user, 'nama' => $nama, 'email' => $item->email, 'role' => $userRole]) }})"
                                                class="min-h-[44px] inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 rounded-xl text-xs font-semibold transition-all border border-slate-200 focus-visible:outline-2 focus-visible:outline-emerald-600"
                                                title="Ubah Password Pengguna Ini">
                                            <i class="fas fa-key text-[11px] text-amber-500"></i>
                                            <span>Reset</span>
                                        </button>
                                    @endif

                                    {{-- Tombol Menu Titik Tiga (â‹¯) --}}
                                    @if($hasMoreActions)
                                        <button type="button"
                                                @click="openMenu($event, {{ json_encode($menuPayload) }})"
                                                class="min-h-[44px] min-w-[44px] w-11 h-11 rounded-xl flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-800 border border-slate-200 transition-colors shrink-0 focus-visible:outline-2 focus-visible:outline-emerald-600"
                                                aria-haspopup="menu"
                                                :aria-expanded="activeMenu?.id === {{ $item->id_user }}"
                                                aria-controls="floating-user-menu"
                                                aria-label="Menu Aksi Lainnya {{ $nama }}"
                                                title="Aksi Lainnya">
                                            <i class="fas fa-ellipsis-v text-xs" aria-hidden="true"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl mb-3">
                                        <i class="fas fa-user-slash"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700">Tidak ada pengguna ditemukan</p>
                                    <p class="text-xs text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau filter role di atas.</p>
                                    <a href="{{ route('beranda.datauser') }}" class="mt-3 min-h-[44px] px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl inline-flex items-center gap-1.5 transition-colors focus-visible:outline-2 focus-visible:outline-emerald-600">
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

                $isSelf = auth()->check() && (auth()->id() == $item->id_user);
                $isAdminRole = ($userRole === 'Admin');
                $isOnlyAdmin = $isAdminRole && (($totalAdmin ?? 1) <= 1);

                $isActiveAccount = true;
                if (isset($item->is_active)) {
                    $isActiveAccount = (bool) $item->is_active;
                } elseif (isset($item->status)) {
                    $isActiveAccount = in_array(strtolower($item->status), ['aktif', 'active', '1', 1]);
                }

                $hasActiveCasis = false;
                if ($userRole === 'Orang Tua') {
                    $hasActiveCasis = $item->calonSiswa()->exists();
                }

                $canDeactivate = false;
                $canDelete = false;

                if (!$isSelf && !$isOnlyAdmin) {
                    if ($isActiveAccount) {
                        $canDeactivate = true;
                        if ($userRole === 'Orang Tua' && !$hasActiveCasis) {
                            $canDelete = true;
                        }
                    } else {
                        $canDelete = true;
                    }
                }

                $hasMoreActions = $canDeactivate || $canDelete;

                $menuPayload = [
                    'id' => $item->id_user,
                    'nama' => $nama,
                    'username' => $item->username ?: ($item->nama ?: ''),
                    'email' => $item->email,
                    'role' => $userRole,
                    'canDeactivate' => $canDeactivate,
                    'canDelete' => $canDelete,
                    'isActive' => $isActiveAccount
                ];
            @endphp
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-sm space-y-3">
                {{-- Header Kartu Mobile --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shrink-0
                            {{ $userRole === 'Admin' ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : ($userRole === 'Panitia' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-blue-100 text-blue-700 border border-blue-200') }}">
                            {{ $initials ?: 'U' }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <h3 class="font-bold text-slate-900 text-base truncate leading-snug">{{ $nama }}</h3>
                                @if($isSelf)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 shrink-0">Anda</span>
                                @endif
                            </div>
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

                {{-- Detail Grid 2x2 --}}
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
                    <div>
                        <span class="text-slate-400 block uppercase text-[10px]">Status Akun</span>
                        <div class="mt-0.5">
                            @if(isset($item->is_active) || isset($item->status))
                                @if($isActiveAccount)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        <span>Nonaktif</span>
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Aktif</span>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <span class="text-slate-400 block uppercase text-[10px]">Terakhir Login</span>
                        <span class="text-slate-700 mt-0.5 block truncate">
                            @if(!empty($item->last_login_at) || !empty($item->last_seen_at))
                                {{ \Carbon\Carbon::parse($item->last_login_at ?? $item->last_seen_at)->timezone('Asia/Makassar')->translatedFormat('d M Y, H.i') }} WITA
                            @else
                                <span class="text-slate-400">Belum pernah</span>
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Baris Tombol Aksi Mobile --}}
                <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                    @if(!$isActiveAccount)
                        <button type="button"
                                @click="openNonaktifModal({{ json_encode($menuPayload) }}, 'aktifkan')"
                                class="flex-1 min-h-[44px] py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-xl text-xs font-semibold transition-all inline-flex items-center justify-center gap-1.5 border border-emerald-200 focus-visible:outline-2 focus-visible:outline-emerald-600">
                            <i class="fas fa-check-circle text-[11px]"></i>
                            <span>Aktifkan Kembali</span>
                        </button>
                    @else
                        <button type="button"
                                @click="openResetModal({{ json_encode(['id' => $item->id_user, 'nama' => $nama, 'email' => $item->email, 'role' => $userRole]) }})"
                                class="flex-1 min-h-[44px] py-2 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 rounded-xl text-xs font-semibold transition-all inline-flex items-center justify-center gap-1.5 border border-slate-200 focus-visible:outline-2 focus-visible:outline-emerald-600">
                            <i class="fas fa-key text-[11px] text-amber-500"></i>
                            <span>Ubah Password</span>
                        </button>
                    @endif

                    @if($hasMoreActions)
                        <button type="button"
                                @click="openMenu($event, {{ json_encode($menuPayload) }})"
                                class="min-h-[44px] min-w-[44px] w-11 h-11 rounded-xl flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-colors shrink-0 focus-visible:outline-2 focus-visible:outline-emerald-600"
                                aria-haspopup="menu"
                                :aria-expanded="activeMenu?.id === {{ $item->id_user }}"
                                aria-controls="floating-user-menu"
                                aria-label="Menu Aksi Lainnya {{ $nama }}">
                            <i class="fas fa-ellipsis-v text-xs" aria-hidden="true"></i>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-6 text-center border border-slate-200/90 text-slate-500 text-sm">
                Tidak ada akun pengguna yang ditemukan.
            </div>
        @endforelse
    </div>

    {{-- ========================================================
         FLOATING POPOVER ACTION MENU (Fixed Position)
         ======================================================== --}}
    <div x-show="activeMenu !== null"
         x-cloak
         id="floating-user-menu"
         @click.outside="closeMenu()"
         @keydown.escape.window="closeMenu()"
         @keydown.arrow-down.prevent="focusNextMenuItem(1)"
         @keydown.arrow-up.prevent="focusNextMenuItem(-1)"
         :style="`position: fixed; top: ${activeMenu?.top}px; left: ${activeMenu?.left}px; width: 200px; z-index: 9999;`"
         class="bg-white rounded-xl shadow-2xl border border-slate-200 py-1.5 focus:outline-none"
         role="menu"
         aria-orientation="vertical">

        {{-- Opsi Nonaktifkan Akun --}}
        <template x-if="activeMenu?.canDeactivate">
            <button type="button"
                    @click="triggerNonaktifkan()"
                    class="w-full text-left px-4 py-2.5 text-xs font-semibold text-amber-800 hover:bg-amber-50 flex items-center gap-2.5 transition-colors min-h-[44px] focus-visible:outline-2 focus-visible:outline-amber-600"
                    role="menuitem" tabindex="0">
                <i class="fas fa-user-slash text-xs text-amber-600 shrink-0" aria-hidden="true"></i>
                <span>Nonaktifkan Akun</span>
            </button>
        </template>

        {{-- Garis Pemisah bila ada Hapus Permanen --}}
        <template x-if="activeMenu?.canDelete && activeMenu?.canDeactivate">
            <div class="border-t border-slate-100 my-1"></div>
        </template>

        {{-- Opsi Hapus Permanen --}}
        <template x-if="activeMenu?.canDelete">
            <button type="button"
                    @click="triggerHapusPermanen()"
                    class="w-full text-left px-4 py-2.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 flex items-center gap-2.5 transition-colors min-h-[44px] focus-visible:outline-2 focus-visible:outline-rose-600"
                    role="menuitem" tabindex="0">
                <i class="fas fa-trash-alt text-xs text-rose-600 shrink-0" aria-hidden="true"></i>
                <span>Hapus Permanen</span>
            </button>
        </template>
    </div>

    {{-- ========================================================
         MODAL 1: UBAH / RESET PASSWORD PENGGUNA
         ======================================================== --}}
    <div x-show="openModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-reset-title"
         role="dialog"
         aria-modal="true"
         @keydown.tab="trapFocus($event, $refs.modalResetRef)">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="closeResetModal()"></div>

        {{-- Modal Dialog --}}
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-ref="modalResetRef"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100 p-6 space-y-5"
                 @click.stop>
                
                {{-- Header Modal --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0 border border-amber-100" aria-hidden="true">
                            <i class="fas fa-key"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900" id="modal-reset-title">Ubah Password Akun</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Tetapkan password baru untuk akun pengguna ini.</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="closeResetModal()"
                            class="min-h-[44px] min-w-[44px] w-11 h-11 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors shrink-0 focus-visible:outline-2 focus-visible:outline-emerald-600"
                            aria-label="Tutup dialog ubah password">
                        <i class="fas fa-times text-sm" aria-hidden="true"></i>
                    </button>
                </div>

                {{-- Info Pengguna --}}
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 flex items-center justify-between gap-3 text-xs">
                    <div class="min-w-0">
                        <p class="font-bold text-slate-800 truncate" x-text="selectedUser ? selectedUser.nama : ''"></p>
                        <p class="text-slate-500 truncate" x-text="selectedUser ? selectedUser.email : ''"></p>
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
                                class="min-h-[44px] px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors focus-visible:outline-2 focus-visible:outline-emerald-600">
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="isMismatch || newPassword.length < 6"
                                class="min-h-[44px] px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-emerald-600/20 inline-flex items-center gap-2 focus-visible:outline-2 focus-visible:outline-emerald-600">
                            <i class="fas fa-check"></i>
                            <span>Simpan Password</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================================================
         MODAL 2: KONFIRMASI NONAKTIFKAN / AKTIFKAN AKUN
         ======================================================== --}}
    <div x-show="modalNonaktif"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-nonaktif-title"
         aria-describedby="modal-nonaktif-desc"
         role="alertdialog"
         aria-modal="true"
         @keydown.tab="trapFocus($event, $refs.modalNonaktifRef)">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="closeNonaktifModal()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-ref="modalNonaktifRef"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100 p-6 space-y-5"
                 @click.stop>
                
                {{-- Header Modal --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0 border border-amber-100" aria-hidden="true">
                            <i class="fas fa-user-slash"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900" id="modal-nonaktif-title"
                                x-text="modeNonaktif === 'aktifkan' ? 'Aktifkan Kembali Akun' : 'Nonaktifkan Akun'"></h3>
                            <p class="text-xs text-slate-500 mt-0.5">Konfirmasi perubahan status akses pengguna.</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="closeNonaktifModal()"
                            class="min-h-[44px] min-w-[44px] w-11 h-11 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors shrink-0 focus-visible:outline-2 focus-visible:outline-emerald-600"
                            aria-label="Tutup dialog konfirmasi">
                        <i class="fas fa-times text-sm" aria-hidden="true"></i>
                    </button>
                </div>

                {{-- Deskripsi Pesan Konfirmasi --}}
                <div class="text-sm text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-100" id="modal-nonaktif-desc">
                    <template x-if="modeNonaktif === 'nonaktifkan'">
                        <p>
                            Akun <strong class="text-slate-800" x-text="selectedUser?.nama"></strong> akan dinonaktifkan. Pengguna tidak dapat login sampai diaktifkan kembali. Data terhubung tidak akan dihapus.
                        </p>
                    </template>
                    <template x-if="modeNonaktif === 'aktifkan'">
                        <p>
                            Akun <strong class="text-slate-800" x-text="selectedUser?.nama"></strong> akan diaktifkan kembali. Pengguna dapat login kembali ke sistem PPDB.
                        </p>
                    </template>
                </div>

                {{-- Form Konfirmasi Status --}}
                <form :action="'/admin/data/user/' + (selectedUser?.id || '') + '/toggle-status'"
                      method="POST"
                      class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    @csrf
                    {{-- TODO: Route backend POST/PATCH /admin/data/user/{id}/toggle-status atau nonaktifkan belum diimplementasikan di web.php dan PengaturanController. --}}

                    <button type="button"
                            x-ref="btnBatalNonaktif"
                            @click="closeNonaktifModal()"
                            class="min-h-[44px] px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors focus-visible:outline-2 focus-visible:outline-emerald-600">
                        Batal
                    </button>
                    <button type="submit"
                            class="min-h-[44px] px-5 py-2.5 text-white text-sm font-semibold rounded-xl transition-all shadow-sm inline-flex items-center gap-2 focus-visible:outline-2"
                            :class="modeNonaktif === 'aktifkan' ? 'bg-emerald-600 hover:bg-emerald-700 focus-visible:outline-emerald-600' : 'bg-amber-600 hover:bg-amber-700 focus-visible:outline-amber-600'">
                        <i class="fas" :class="modeNonaktif === 'aktifkan' ? 'fa-check' : 'fa-user-slash'"></i>
                        <span x-text="modeNonaktif === 'aktifkan' ? 'Aktifkan Kembali' : 'Nonaktifkan'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================================================
         MODAL 3: KONFIRMASI HAPUS AKUN SECARA PERMANEN
         ======================================================== --}}
    <div x-show="modalHapus"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-hapus-title"
         aria-describedby="modal-hapus-desc"
         role="alertdialog"
         aria-modal="true"
         @keydown.tab="trapFocus($event, $refs.modalHapusRef)">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="closeHapusModal()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-ref="modalHapusRef"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100 p-6 space-y-5"
                 @click.stop>
                
                {{-- Header Modal --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0 border border-rose-100" aria-hidden="true">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900" id="modal-hapus-title">Hapus Akun Secara Permanen</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Tindakan destruktif ini tidak dapat dibatalkan.</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="closeHapusModal()"
                            class="min-h-[44px] min-w-[44px] w-11 h-11 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors shrink-0 focus-visible:outline-2 focus-visible:outline-rose-600"
                            aria-label="Tutup dialog konfirmasi hapus">
                        <i class="fas fa-times text-sm" aria-hidden="true"></i>
                    </button>
                </div>

                {{-- Peringatan & Cek Relasi Server --}}
                <div class="space-y-3 text-sm text-slate-600" id="modal-hapus-desc">
                    <div class="bg-rose-50 border border-rose-200 text-rose-800 p-3.5 rounded-xl text-xs space-y-1.5">
                        <div class="flex items-center gap-2 font-bold text-rose-900">
                            <i class="fas fa-exclamation-circle text-rose-600"></i>
                            <span>Peringatan Keamanan Keras:</span>
                        </div>
                        <p class="leading-relaxed">
                            Akun <strong class="text-rose-950" x-text="selectedUser?.nama"></strong> akan dihapus permanen dari sistem. Server akan memvalidasi bahwa akun tidak memiliki data aktif (calon siswa terdaftar, riwayat pembayaran, atau nilai seleksi).
                        </p>
                    </div>

                    {{-- Konfirmasi Ketik Username --}}
                    <div class="space-y-1.5 pt-1">
                        <label class="text-xs font-semibold text-slate-700 block">
                            Ketik <span class="font-mono font-bold text-slate-900 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200" x-text="confirmTarget"></span> untuk mengonfirmasi:
                        </label>
                        <input type="text"
                               x-model="inputConfirmUsername"
                               placeholder="Ketik persis di sini..."
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all font-mono">
                    </div>
                </div>

                {{-- Form Hapus Permanen --}}
                <form :action="'/admin/data/user/' + (selectedUser?.id || '') + '/delete'"
                      method="POST"
                      class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    @csrf
                    @method('DELETE')
                    {{-- TODO: Route backend DELETE /admin/data/user/{id} belum diimplementasikan di web.php dan PengaturanController. Form ini siap digunakan begitu route backend selesai dibuat. --}}

                    <button type="button"
                            x-ref="btnBatalHapus"
                            @click="closeHapusModal()"
                            class="min-h-[44px] px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors focus-visible:outline-2 focus-visible:outline-emerald-600">
                        Batal
                    </button>
                    <button type="submit"
                            :disabled="!isHapusValid"
                            class="min-h-[44px] px-5 py-2.5 bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-rose-600/20 inline-flex items-center gap-2 focus-visible:outline-2 focus-visible:outline-rose-600">
                        <i class="fas fa-trash-alt"></i>
                        <span>Hapus Permanen</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
