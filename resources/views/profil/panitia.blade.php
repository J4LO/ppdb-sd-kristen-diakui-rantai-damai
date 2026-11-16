@extends('layout.tampilanpanitia')

@section('content')
<div class="min-h-screen bg-slate-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800">
                Profil Panitia
            </h1>

            <p class="text-slate-500 mt-1">
                Informasi akun panitia PPDB.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Sidebar --}}
            <div class="lg:col-span-1">

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

                    {{-- Cover --}}
                    <div class="h-28 bg-gradient-to-r from-emerald-600 to-green-500"></div>

                    <div class="px-6 pb-6 relative">

                        {{-- Avatar --}}
                        <div class="flex justify-center">
                            <div class="-mt-16">
                                <img src="{{ asset('template/dist/img/user0-128x128.jpg') }}"
                                    alt="avatar"
                                    class="w-32 h-32 rounded-full border-4 border-white shadow-md object-cover">
                            </div>
                        </div>

                        {{-- Name --}}
                        <div class="text-center mt-4">
                            <h2 class="text-xl font-bold text-slate-800">
                                {{ $panitia->nama }}
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                Panitia PPDB
                            </p>
                        </div>

                        {{-- Status Card --}}
                        <div class="mt-6">
                            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">

                                <div class="flex items-center gap-3">

                                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                        <i class="fas fa-user-check text-emerald-600"></i>
                                    </div>

                                    <div>
                                        <p class="text-sm text-slate-500">
                                            Status Akun
                                        </p>

                                        <p class="font-semibold text-emerald-700">
                                            {{ $panitia->status }}
                                        </p>
                                    </div>

                                </div>

                            </div>
                        </div>

                        {{-- Tombol Logout / Keluar dari Akun --}}
                        <div class="mt-6 pt-5 border-t border-slate-100">
                            <a href="{{ route('logout') }}"
                               onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')"
                               class="w-full inline-flex items-center justify-center gap-2.5 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 hover:text-rose-800 border border-rose-200 hover:border-rose-300 rounded-xl font-semibold text-sm transition-colors min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2"
                               title="Keluar dari Akun Panitia">
                                <i class="fas fa-sign-out-alt text-rose-600" aria-hidden="true"></i>
                                <span>Keluar dari Akun</span>
                            </a>
                        </div>

                    </div>
                </div>

            </div>

            {{-- Detail --}}
            <div class="lg:col-span-2">

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

                    {{-- Header --}}
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800">
                            Biodata Panitia
                        </h3>

                        <p class="text-sm text-slate-500 mt-1">
                            Detail informasi akun panitia PPDB.
                        </p>
                    </div>

                    {{-- Body --}}
                    <div class="p-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Nama --}}
                            <div>
                                <label class="text-sm font-semibold text-slate-500">
                                    Nama Lengkap
                                </label>

                                <div class="mt-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 font-medium">
                                    {{ $panitia->nama }}
                                </div>
                            </div>

                            {{-- Role --}}
                            <div>
                                <label class="text-sm font-semibold text-slate-500">
                                    Role
                                </label>

                                <div class="mt-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 font-medium">
                                    {{ $panitia->user->role }}
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="md:col-span-2">
                                <label class="text-sm font-semibold text-slate-500">
                                    Email
                                </label>

                                <div class="mt-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 font-medium break-all">
                                    {{ $panitia->user->email }}
                                </div>
                            </div>

                            {{-- Jenis Kelamin --}}
                            <div>
                                <label class="text-sm font-semibold text-slate-500">
                                    Jenis Kelamin
                                </label>

                                <div class="mt-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 font-medium">
                                    {{ $panitia->jenis_kelamin ?? 'Belum Ada' }}
                                </div>
                            </div>

                            {{-- Status --}}
                            <div>
                                <label class="text-sm font-semibold text-slate-500">
                                    Status
                                </label>

                                <div class="mt-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 font-medium">
                                    {{ $panitia->status ?? 'Belum Ada' }}
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
</div>
@endsection