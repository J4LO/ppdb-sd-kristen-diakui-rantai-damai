@extends('layout.tampilan')

@section('page-title', 'Tambah Tahun Ajaran')

@section('content')
<div class="p-6 max-w-4xl mx-auto">

    <div class="mb-8 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Tambah Tahun Ajaran</h1>
            <p class="text-slate-500 text-sm mt-1">Isi detail tahun ajaran baru untuk sistem PPDB.</p>
        </div>
        <a href="{{ route('beranda.tahun') }}" class="px-5 py-2 bg-white border border-slate-300 text-slate-700 font-semibold text-sm rounded-xl hover:bg-slate-50 transition-colors shadow-sm flex items-center gap-2">
            <i class="fas fa-arrow-left text-xs"></i> Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0">
                <i class="fas fa-calendar-plus"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-800">Form Tahun Ajaran Baru</h2>
                <p class="text-slate-500 text-xs">Semua field wajib diisi dengan benar.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('tahun.simpan') }}" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Tahun Ajaran --}}
                <div class="md:col-span-2">
                    <label for="tahun_ajar" class="block text-sm font-semibold text-slate-700 mb-2">
                        Tahun Ajaran <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="tahun_ajar" name="tahun_ajar"
                        class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-700 font-semibold text-lg focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all placeholder-slate-400"
                        placeholder="Contoh: 2025/2026" required>
                    <p class="text-slate-400 text-xs mt-1">Format: YYYY/YYYY (contoh: 2025/2026)</p>
                </div>

                {{-- Mulai Pendaftaran --}}
                <div>
                    <label for="mulai_pendaftaran" class="block text-sm font-semibold text-slate-700 mb-2">
                        Tanggal Mulai Pendaftaran <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="mulai_pendaftaran" name="mulai_pendaftaran"
                        class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                        required>
                </div>

                {{-- Batas Pendaftaran --}}
                <div>
                    <label for="batas_pendaftaran" class="block text-sm font-semibold text-slate-700 mb-2">
                        Tanggal Batas Pendaftaran <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="batas_pendaftaran" name="batas_pendaftaran"
                        class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                        required>
                </div>

                {{-- Tanggal Seleksi --}}
                <div>
                    <label for="tgl_seleksi" class="block text-sm font-semibold text-slate-700 mb-2">
                        Tanggal Seleksi <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="tgl_seleksi" name="tgl_seleksi"
                        class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                        required>
                </div>

                {{-- Kuota --}}
                <div>
                    <label for="kuota" class="block text-sm font-semibold text-slate-700 mb-2">
                        Kuota Penerimaan <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="kuota" name="kuota" min="1"
                        class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                        placeholder="Contoh: 30" required>
                    <p class="text-slate-400 text-xs mt-1">Jumlah maksimal siswa yang dapat diterima</p>
                </div>

                {{-- Biaya Pendaftaran --}}
                <div class="md:col-span-2">
                    <label for="biaya_pendaftaran" class="block text-sm font-semibold text-slate-700 mb-2">
                        Biaya Formulir Pendaftaran (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="biaya_pendaftaran" name="biaya_pendaftaran" min="0" step="5000"
                        class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all font-semibold"
                        placeholder="Contoh: 150000" value="150000" required>
                    <p class="text-slate-400 text-xs mt-1">Biaya pembayaran awal saat pendaftaran akun casis</p>
                </div>

                {{-- Rincian Biaya Masuk / Daftar Ulang --}}
                <div class="md:col-span-2 bg-slate-50/80 rounded-2xl p-5 border border-slate-200">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-xl bg-green-100 text-green-700 flex items-center justify-center shrink-0">
                            <i class="fas fa-coins text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Rincian Biaya Masuk / Daftar Ulang</h3>
                            <p class="text-slate-500 text-xs">Atur nominal ketiga komponen biaya di bawah. Total biaya daftar ulang akan terhitung secara otomatis.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        {{-- 1. Uang Pembangunan & Sarana --}}
                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                            <label for="biaya_pembangunan" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                1. Uang Pembangunan & Sarana <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-semibold text-xs">Rp</span>
                                <input type="number" id="biaya_pembangunan" name="biaya_pembangunan" min="0" step="1000"
                                    value="600000"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-3 py-2.5 text-slate-800 font-bold focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all text-sm biaya-komponen"
                                    required>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1.5 leading-tight">Gedung & fasilitas penunjang belajar</p>
                        </div>

                        {{-- 2. Buku Paket Pelajaran & Modul --}}
                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                            <label for="biaya_buku" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                2. Buku Paket & Modul <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-semibold text-xs">Rp</span>
                                <input type="number" id="biaya_buku" name="biaya_buku" min="0" step="1000"
                                    value="200000"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-3 py-2.5 text-slate-800 font-bold focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all text-sm biaya-komponen"
                                    required>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1.5 leading-tight">Buku paket & modul pembelajaran 1 TA</p>
                        </div>

                        {{-- 3. Seragam Sekolah & Atribut --}}
                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                            <label for="biaya_seragam" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                3. Seragam Sekolah & Atribut <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-semibold text-xs">Rp</span>
                                <input type="number" id="biaya_seragam" name="biaya_seragam" min="0" step="1000"
                                    value="200000"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-3 py-2.5 text-slate-800 font-bold focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all text-sm biaya-komponen"
                                    required>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1.5 leading-tight">Seragam lengkap beserta atribut siswa</p>
                        </div>
                    </div>

                    {{-- Total Otomatis --}}
                    <div class="bg-green-50/70 border border-green-200/80 rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div>
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-green-800">
                                <i class="fas fa-calculator text-green-600"></i> Total Biaya Masuk / Daftar Ulang
                            </span>
                            <p class="text-xs text-green-700/80 mt-0.5">Kalkulasi otomatis dari penjumlahan ketiga komponen di atas.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="hidden" id="biaya_daftar_ulang" name="biaya_daftar_ulang" value="1000000">
                            <div id="display_total_biaya" class="text-xl sm:text-2xl font-black text-green-700 tracking-tight">
                                Rp 1.000.000
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="md:col-span-2">
                    <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">
                        Status Tahun Ajaran <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status"
                        class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-700 font-medium focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                        required>
                        <option value="" disabled selected>-- Pilih Status --</option>
                        <option value="Belum Dimulai">📅 Belum Dimulai</option>
                        <option value="Berlangsung">🟢 Berlangsung (Aktif)</option>
                        <option value="Berakhir">✅ Berakhir</option>
                    </select>
                    <p class="text-slate-400 text-xs mt-1">Hanya satu tahun ajaran yang boleh berstatus <strong>"Berlangsung"</strong> pada satu waktu.</p>
                </div>

                {{-- Nama Kepala Sekolah --}}
                <div class="md:col-span-2">
                    <label for="nama_kepala_sekolah" class="block text-sm font-semibold text-slate-700 mb-2">
                        Nama Kepala Sekolah
                    </label>
                    <input type="text" id="nama_kepala_sekolah" name="nama_kepala_sekolah"
                        value="{{ old('nama_kepala_sekolah', config('sekolah.kepala_sekolah', 'Serni, S.Pd.K')) }}"
                        maxlength="100"
                        placeholder="cth. Serni, S.Pd.K"
                        class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-700 font-medium focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all placeholder-slate-400">
                    <p class="text-slate-400 text-xs mt-1">Nama ini dicantumkan sebagai penandatangan lembar laporan cetak dan PDF.</p>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-100 flex gap-3">
                <button type="submit"
                    class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl transition-all shadow-md shadow-green-600/20 hover:-translate-y-0.5 flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Tahun Ajaran
                </button>
                <a href="{{ route('beranda.tahun') }}"
                    class="px-6 py-3 bg-white border border-slate-300 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputPembangunan = document.getElementById('biaya_pembangunan');
    const inputBuku = document.getElementById('biaya_buku');
    const inputSeragam = document.getElementById('biaya_seragam');
    const inputTotal = document.getElementById('biaya_daftar_ulang');
    const displayTotal = document.getElementById('display_total_biaya');

    function hitungTotal() {
        const pembangunan = parseFloat(inputPembangunan ? inputPembangunan.value : 0) || 0;
        const buku = parseFloat(inputBuku ? inputBuku.value : 0) || 0;
        const seragam = parseFloat(inputSeragam ? inputSeragam.value : 0) || 0;
        const total = Math.max(0, Math.round(pembangunan + buku + seragam));

        if (inputTotal) {
            inputTotal.value = total;
        }
        if (displayTotal) {
            displayTotal.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
    }

    [inputPembangunan, inputBuku, inputSeragam].forEach(input => {
        if (input) {
            input.addEventListener('input', hitungTotal);
            input.addEventListener('change', hitungTotal);
            input.addEventListener('keyup', hitungTotal);
        }
    });

    hitungTotal();
});
</script>
@endsection
