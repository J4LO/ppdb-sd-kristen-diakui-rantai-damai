@extends('layout.tampilan')

@section('content')
<div class="min-h-screen bg-slate-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800">
                Profil Admin
            </h1>

            <p class="text-slate-500 mt-1">
                Informasi akun administrator sistem PPDB.
            </p>
        </div>



        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Sidebar --}}
            <div class="lg:col-span-1">

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

                    {{-- Cover --}}
                    <div class="h-28 bg-gradient-to-r from-blue-600 to-indigo-500"></div>

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
                                {{ $user->name }}
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                Administrator Sistem
                            </p>
                        </div>

                        {{-- Role Card --}}
                        <div class="mt-6">
                            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                                <div class="flex items-center gap-3">

                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-user-shield text-blue-600"></i>
                                    </div>

                                    <div>
                                        <p class="text-sm text-slate-500">
                                            Hak Akses
                                        </p>

                                        <p class="font-semibold text-blue-700">
                                            {{ $user->role }}
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
                               title="Keluar dari Akun">
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
                            Biodata Admin
                        </h3>

                        <p class="text-sm text-slate-500 mt-1">
                            Detail informasi akun administrator.
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
                                    {{ $user->name }}
                                </div>
                            </div>

                            {{-- Role --}}
                            <div>
                                <label class="text-sm font-semibold text-slate-500">
                                    Role
                                </label>

                                <div class="mt-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 font-medium">
                                    {{ $user->role }}
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="md:col-span-2">
                                <label class="text-sm font-semibold text-slate-500">
                                    Email
                                </label>

                                <div class="mt-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 font-medium break-all">
                                    {{ $user->email }}
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