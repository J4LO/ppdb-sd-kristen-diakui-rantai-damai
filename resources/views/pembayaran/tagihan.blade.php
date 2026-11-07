@extends('layout.tampilan')

@section('page-title', 'Verifikasi Pembayaran')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    {{-- Page Header --}}
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold font-heading">Verifikasi Pembayaran</h1>
            <p class="text-slate-500 text-sm mt-1">Periksa bukti transfer dan perbarui status pembayaran calon siswa.</p>
        </div>
        
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 font-medium transition-colors">Dashboard</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="/admin/pembayaran" class="hover:text-blue-600 font-medium transition-colors">Data Pembayaran</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-slate-800 font-medium">Verifikasi</span>
        </div>
    </div>

    @include('layout.alert')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Preview Bukti Pembayaran --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4 bg-slate-50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <i class="fas fa-image text-sm"></i>
                    </div>
                    <h3 class="font-bold text-slate-800">Bukti Transfer</h3>
                </div>
                <div class="p-6">
                    @if($data->bukti_pembayaran)
                        @php
                        $file_name = $data->bukti_pembayaran;
                        $file_path = asset('storage/pembayaran/' . $file_name);
                        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        @endphp
                        
                        <div class="rounded-xl overflow-hidden bg-slate-100 border border-slate-200">
                            @if(in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                <a href="{{ $file_path }}" target="_blank" title="Klik untuk memperbesar">
                                    <img src="{{ $file_path }}" alt="Bukti Pembayaran" class="w-full h-auto object-contain max-h-[420px] hover:opacity-90 transition-opacity">
                                </a>
                            @else
                                <div class="p-8 text-center">
                                    <i class="fas fa-file-pdf text-red-500 text-6xl mb-4"></i>
                                    <p class="text-sm font-medium text-slate-700 mb-4">{{ $file_name }}</p>
                                    <a href="{{ $file_path }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium hover:bg-slate-700 transition-colors">
                                        <i class="fas fa-external-link-alt"></i> Buka Dokumen
                                    </a>
                                </div>
                            @endif
                        </div>
                        <p class="text-xs text-center text-slate-500 mt-3">
                            <i class="fas fa-info-circle mr-1"></i> Klik gambar di atas untuk membuka ukuran penuh.
                        </p>
                    @elseif($data->transaction_id || $data->snap_token)
                        <div class="p-6 bg-blue-50 border border-blue-200 rounded-2xl">
                            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-blue-100">
                                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm">Pembayaran Midtrans Gateway</h4>
                                    <p class="text-xs text-slate-500">Otomatisasi Verifikasi Real-time</p>
                                </div>
                            </div>
                            <div class="space-y-2.5 text-xs">
                                <div class="flex justify-between py-1 border-b border-blue-100/60">
                                    <span class="text-slate-500">Transaction ID:</span>
                                    <span class="font-mono font-semibold text-slate-800">{{ $data->transaction_id ?: '-' }}</span>
                                </div>
                                <div class="flex justify-between py-1 border-b border-blue-100/60">
                                    <span class="text-slate-500">Metode / Kanal:</span>
                                    <span class="font-bold uppercase text-slate-800">{{ $data->metode_pembayaran ?: 'Midtrans Snap' }}</span>
                                </div>
                                @if($data->va_number)
                                <div class="flex justify-between py-1 border-b border-blue-100/60">
                                    <span class="text-slate-500">Nomor VA:</span>
                                    <span class="font-mono font-bold text-blue-700">{{ $data->va_number }}</span>
                                </div>
                                @endif
                                <div class="flex justify-between py-1 border-b border-blue-100/60">
                                    <span class="text-slate-500">Waktu Lunas:</span>
                                    <span class="font-semibold text-green-700">{{ $data->waktu_lunas ? \Carbon\Carbon::parse($data->waktu_lunas)->format('d/m/Y H:i') : '-' }}</span>
                                </div>
                                <div class="flex justify-between pt-1">
                                    <span class="text-slate-500">Status Gateway:</span>
                                    <span class="font-bold px-2 py-0.5 rounded-full {{ in_array($data->status_pembayaran, ['Lunas', 'Diterima']) ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $data->status_pembayaran }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12 px-4 bg-slate-50 rounded-xl border-2 border-dashed border-slate-200">
                            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mx-auto mb-3">
                                <i class="fas fa-image text-2xl"></i>
                            </div>
                            <h4 class="text-sm font-bold text-slate-700 mb-1">Belum Ada Bukti</h4>
                            <p class="text-xs text-slate-500">Calon siswa ini belum mengunggah bukti transfer pembayaran.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Form Verifikasi --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4 bg-slate-50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-green-100 text-green-600 flex items-center justify-center">
                        <i class="fas fa-clipboard-check text-sm"></i>
                    </div>
                    <h3 class="font-bold text-slate-800">Detail & Verifikasi Status</h3>
                </div>
                
                <form method="post" action="/admin/pembayaran/proses/{{ $data->id_pembayaran }}">
                    @csrf
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            {{-- Order ID --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Order ID</label>
                                <input type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-700 font-mono font-semibold" value="{{ $data->order_id ?? '-' }}" readonly>
                            </div>

                            {{-- Nomor Registrasi --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Nomor Registrasi</label>
                                <input type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-700 font-mono font-semibold" value="{{ $data->calonSiswa->no_registrasi ?? '-' }}" readonly>
                            </div>
                            
                            {{-- Nama --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Nama Calon Siswa</label>
                                <input type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-700 font-semibold" value="{{ $data->calonSiswa->nama_calon_siswa ?? ($data->casis->nama ?? '-') }}" readonly>
                            </div>
                            
                            {{-- Nominal --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Nominal Pembayaran</label>
                                <input type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-amber-600 font-bold" value="Rp {{ number_format($data->jml_pembayaran ?? 0, 0, ',', '.') }}" readonly>
                            </div>
                            
                            {{-- Tanggal Submit --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Tanggal Pembayaran</label>
                                <input type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-700 font-medium" value="{{ format_school_time($data->tgl_pembayaran, 'd F Y, H:i') }}" readonly>
                            </div>

                            {{-- Metode Pembayaran --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Metode Pembayaran</label>
                                <input type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-700 font-medium" value="{{ $data->metode_pembayaran ?? 'Transfer Bank' }}" readonly>
                            </div>

                            {{-- Pilihan Status Verifikasi --}}
                            <div class="md:col-span-2 pt-4 border-t border-slate-100">
                                <label class="block text-sm font-semibold text-slate-700 mb-3">Status Verifikasi <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    {{-- Lunas --}}
                                    <label class="flex items-center gap-3 p-4 border rounded-xl cursor-pointer transition-all hover:bg-slate-50 {{ in_array($data->status_pembayaran, ['Lunas', 'Diterima']) ? 'border-green-500 bg-green-50/50 ring-1 ring-green-500' : 'border-slate-200' }}">
                                        <input type="radio" name="status_pembayaran" value="Lunas" class="w-4 h-4 text-green-600 border-slate-300 focus:ring-green-500" {{ in_array($data->status_pembayaran, ['Lunas', 'Diterima']) ? 'checked' : '' }} required>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-800 flex items-center gap-1.5"><i class="fas fa-check-circle text-green-500"></i> Lunas</span>
                                            <span class="text-xs text-slate-500">Bukti valid & sah</span>
                                        </div>
                                    </label>
                                    
                                    {{-- Menunggu --}}
                                    <label class="flex items-center gap-3 p-4 border rounded-xl cursor-pointer transition-all hover:bg-slate-50 {{ in_array($data->status_pembayaran, ['Menunggu', 'Pending']) ? 'border-amber-500 bg-amber-50/50 ring-1 ring-amber-500' : 'border-slate-200' }}">
                                        <input type="radio" name="status_pembayaran" value="Menunggu" class="w-4 h-4 text-amber-600 border-slate-300 focus:ring-amber-500" {{ in_array($data->status_pembayaran, ['Menunggu', 'Pending']) ? 'checked' : '' }} required>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-800 flex items-center gap-1.5"><i class="fas fa-clock text-amber-500"></i> Menunggu</span>
                                            <span class="text-xs text-slate-500">Sedang diperiksa</span>
                                        </div>
                                    </label>

                                    {{-- Ditolak --}}
                                    <label class="flex items-center gap-3 p-4 border rounded-xl cursor-pointer transition-all hover:bg-slate-50 {{ $data->status_pembayaran === 'Ditolak' ? 'border-red-500 bg-red-50/50 ring-1 ring-red-500' : 'border-slate-200' }}">
                                        <input type="radio" name="status_pembayaran" value="Ditolak" class="w-4 h-4 text-red-600 border-slate-300 focus:ring-red-500" {{ $data->status_pembayaran === 'Ditolak' ? 'checked' : '' }} required>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-800 flex items-center gap-1.5"><i class="fas fa-times-circle text-red-500"></i> Ditolak</span>
                                            <span class="text-xs text-slate-500">Bukti tidak valid</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Form Actions --}}
                    <div class="p-6 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="/admin/pembayaran" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 hover:text-slate-800 transition-colors">
                            Kembali
                        </a>
                        <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-600/30 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                            <i class="fas fa-save"></i> Simpan Status Verifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
