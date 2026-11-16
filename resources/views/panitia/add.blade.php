@extends('layout.tampilan')

@section('page-title', 'Tambah Panitia')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Tambah Data Panitia</h1>
            <p class="text-slate-500 text-sm mt-1">Lengkapi form berikut untuk menambahkan panitia PPDB baru.</p>
        </div>
        <a href="/admin/data/panitia" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800 rounded-xl text-sm font-medium transition-colors">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Main Form Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 sm:p-8">
            <form method="post" action="/admin/data/panitia/proses" class="space-y-8">
                @csrf

                {{-- Group: Informasi Akun --}}
                <div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-user-shield text-sm"></i>
                        </div>
                        Informasi Akun
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-0 md:pl-10">
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-slate-700">Username <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required placeholder="Masukkan username" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-slate-700">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" required placeholder="admin@sekolah.com" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                        </div>
                        <div class="space-y-1.5 md:col-span-2" x-data="{ 
                                password: '',
                                get strength() {
                                    if (this.password.length === 0) return 0;
                                    if (this.password.length < 8) return 1;
                                    let score = 1;
                                    if (this.password.length >= 8) score++;
                                    if (this.password.match(/[A-Z]/) && this.password.match(/[0-9]/)) score++;
                                    return score;
                                }
                            }">
                            <label class="text-sm font-medium text-slate-700">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" x-model="password" required minlength="8" placeholder="Minimal 8 karakter" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                            
                            {{-- Password Strength Indicator --}}
                            <div class="pt-1 space-y-2">
                                <div class="flex gap-1.5 h-1.5 w-full">
                                    <div class="h-full flex-1 rounded-full transition-colors duration-300" :class="strength >= 1 ? (strength === 1 ? 'bg-red-500' : (strength === 2 ? 'bg-amber-500' : 'bg-green-500')) : 'bg-slate-200'"></div>
                                    <div class="h-full flex-1 rounded-full transition-colors duration-300" :class="strength >= 2 ? (strength === 2 ? 'bg-amber-500' : 'bg-green-500') : 'bg-slate-200'"></div>
                                    <div class="h-full flex-1 rounded-full transition-colors duration-300" :class="strength >= 3 ? 'bg-green-500' : 'bg-slate-200'"></div>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-500" x-text="password.length + ' / 8 minimum karakter'"></span>
                                    <span class="font-medium" 
                                        x-show="password.length > 0"
                                        :class="{
                                            'text-red-500': strength === 1,
                                            'text-amber-500': strength === 2,
                                            'text-green-500': strength === 3
                                        }"
                                        x-text="strength === 1 ? 'Lemah' : (strength === 2 ? 'Sedang' : 'Kuat')">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100">

                {{-- Group: Profil Panitia --}}
                <div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-id-card text-sm"></i>
                        </div>
                        Profil Kepanitiaan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-0 md:pl-10">
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama" required placeholder="Nama lengkap panitia" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-slate-700">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select name="jenis_kelamin" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all appearance-none">
                                    <option value="" disabled selected>Pilih jenis kelamin</option>
                                    <option value="Laki-Laki">Laki-Laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-slate-700">No. HP / WhatsApp <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                            <div class="relative">
                                <input type="text" name="no_hp" placeholder="Contoh: 081234567890" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fab fa-whatsapp text-emerald-500 text-sm"></i>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-slate-700">Status Tugas <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select name="status" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all appearance-none">
                                    <option value="Aktif" selected>Aktif</option>
                                    <option value="Non Aktif">Non-Aktif</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-6 mt-6 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-green-600 text-white hover:bg-green-700 rounded-xl text-sm font-medium transition-all shadow-sm shadow-green-600/20 active:scale-[0.98]">
                        <i class="fas fa-save"></i> Simpan Data
                    </button>
                    <a href="/admin/data/panitia" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-800 rounded-xl text-sm font-medium transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection