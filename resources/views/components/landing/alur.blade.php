<section id="pendaftaran" class="py-20 bg-slate-50 border-y border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-green-600 font-bold tracking-wide uppercase text-sm mb-2">Informasi PPDB {{ $aktivTahunAjaran->tahun_ajar ?? date('Y').'/'.(date('Y')+1) }}</h2>
            <h3 class="font-heading text-3xl md:text-4xl font-bold text-slate-800 mb-4">Syarat & Alur Pendaftaran</h3>
            <p class="text-slate-600 text-lg">Pendaftaran dilakukan secara online melalui portal ini. Simak persyaratan dan langkah-langkahnya berikut ini.</p>
        </div>

        <div class="grid lg:grid-cols-12 gap-12">
            {{-- Syarat & Jadwal (Left Column) --}}
            <div class="lg:col-span-5 space-y-8">
                {{-- Syarat Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">
                    <h4 class="font-heading text-xl font-bold text-slate-800 mb-5 flex items-center gap-3">
                        <i class="fas fa-list-check text-green-600"></i> Syarat Pendaftaran
                    </h4>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span class="text-slate-600">Fotokopi Akta Kelahiran</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span class="text-slate-600">Fotokopi Kartu Keluarga (KK)</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span class="text-slate-600">Pas foto terbaru calon siswa</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span class="text-slate-600">Kartu Perlindungan Sosial / KPS (opsional jika ada)</span>
                        </li>
                    </ul>
                    <div class="mt-6 p-4 bg-amber-50 rounded-xl text-sm text-amber-800 border border-amber-100 flex gap-3">
                        <i class="fas fa-info-circle mt-0.5"></i>
                        <p>Dokumen di atas akan diminta untuk diunggah pada saat mengisi formulir pendaftaran online.</p>
                    </div>
                </div>

                {{-- Jadwal Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">
                    <h4 class="font-heading text-xl font-bold text-slate-800 mb-5 flex items-center gap-3">
                        <i class="fas fa-calendar-alt text-amber-500"></i> Jadwal PPDB
                    </h4>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                            <span class="text-slate-600">Pendaftaran</span>
                            <span class="font-semibold text-slate-800">
                                @if($aktivTahunAjaran)
                                    {{ \Carbon\Carbon::parse($aktivTahunAjaran->mulai_pendaftaran)->format('d M') }} - {{ \Carbon\Carbon::parse($aktivTahunAjaran->batas_pendaftaran)->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                            <span class="text-slate-600">Seleksi Observasi</span>
                            <span class="font-semibold text-slate-800 text-right">
                                @if($aktivTahunAjaran)
                                    {{ \Carbon\Carbon::parse($aktivTahunAjaran->tgl_seleksi)->format('d F Y') }}<br>
                                @else
                                    -<br>
                                @endif
                                <span class="text-xs font-normal text-slate-500">08.00-10.00 {{ school_timezone_label() }}</span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">Pengumuman</span>
                            <span class="font-semibold text-slate-800">Melalui Website</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alur Timeline (Right Column) --}}
            <div class="lg:col-span-7">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8 h-full">
                    <h4 class="font-heading text-xl font-bold text-slate-800 mb-8 flex items-center gap-3">
                        <i class="fas fa-route text-blue-500"></i> Alur Pendaftaran Online
                    </h4>

                    <div class="relative border-l-2 border-green-100 ml-3 space-y-8 pb-4">
                        {{-- Step 1 --}}
                        <div class="relative pl-8">
                            <div class="absolute left-[-17px] w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center font-bold shadow-md ring-4 ring-white">1</div>
                            <h5 class="font-bold text-slate-800 text-lg mb-1">Registrasi Akun</h5>
                            <p class="text-slate-600">Buat akun di website PPDB menggunakan email aktif dan data diri Anda.</p>
                        </div>
                        
                        {{-- Step 2 --}}
                        <div class="relative pl-8">
                            <div class="absolute left-[-17px] w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center font-bold shadow-md ring-4 ring-white">2</div>
                            <h5 class="font-bold text-slate-800 text-lg mb-1">Isi Formulir & Upload Dokumen</h5>
                            <p class="text-slate-600">Login ke dashboard, lengkapi data diri calon siswa, data orang tua, dan unggah dokumen persyaratan.</p>
                        </div>

                        {{-- Step 3 --}}
                        <div class="relative pl-8">
                            <div class="absolute left-[-17px] w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center font-bold shadow-md ring-4 ring-white">3</div>
                            <h5 class="font-bold text-slate-800 text-lg mb-1">Biaya Pendaftaran</h5>
                            <p class="text-slate-600">Lakukan pembayaran biaya pendaftaran melalui rekening yang ditentukan dan unggah bukti transfer.</p>
                        </div>

                        {{-- Step 4 --}}
                        <div class="relative pl-8">
                            <div class="absolute left-[-17px] w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold shadow-md ring-4 ring-white">4</div>
                            <h5 class="font-bold text-slate-800 text-lg mb-1">Seleksi Observasi (Offline)</h5>
                            <p class="text-slate-600">Calon siswa mengikuti tes seleksi di sekolah yang meliputi: Kemampuan Membaca, Menulis, dan Berhitung.</p>
                        </div>

                        {{-- Step 5 --}}
                        <div class="relative pl-8">
                            <div class="absolute left-[-17px] w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold shadow-md ring-4 ring-white">5</div>
                            <h5 class="font-bold text-slate-800 text-lg mb-1">Pengumuman & Daftar Ulang</h5>
                            <p class="text-slate-600">Cek status kelulusan melalui portal. Jika dinyatakan lulus, segera lakukan Daftar Ulang.</p>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                        <button @click="registerModalOpen = true" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-xl font-medium shadow-md transition-colors inline-flex items-center gap-2">
                            Mulai Mendaftar Sekarang <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
