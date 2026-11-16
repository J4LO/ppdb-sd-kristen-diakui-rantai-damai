@props(['id', 'status', 'action'])

<div class="modal fade"
    id="statusModal{{ $id }}"
    tabindex="-1"
    role="dialog"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content rounded-2xl border-0 shadow-lg">

            <div class="modal-header border-b border-slate-100 bg-slate-50/50 pb-4 pt-4 rounded-t-2xl flex items-center justify-between">
                <h5 class="modal-title font-bold text-lg text-slate-800 flex items-center gap-2">
                    <i class="fas fa-sync-alt text-blue-500"></i>
                    Status Pendaftaran
                </h5>
                <button type="button"
                    class="close text-slate-400 hover:text-slate-600 transition"
                    data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-6">

                <div class="mb-5 p-4 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                    <p class="text-slate-600 font-medium mb-0">Status Saat Ini</p>
                    <div class="font-bold flex items-center gap-1 {{ $status === 'Berhasil' ? 'text-green-600' : ($status === 'Gagal' ? 'text-red-600' : 'text-slate-600') }}">
                        @if($status === 'Berhasil')
                            <i class="fas fa-check-circle"></i>
                        @elseif($status === 'Gagal')
                            <i class="fas fa-times-circle"></i>
                        @else
                            <i class="fas fa-clock"></i>
                        @endif
                        {{ $status }}
                    </div>
                </div>

                <form action="{{ $action }}" method="POST">
                    @csrf

                    <div class="form-group mb-5">
                        <label class="font-semibold text-slate-700 mb-2 block">
                            Ubah Status Menjadi
                        </label>
                        <select class="form-control rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/20" name="status">
                            <option value="Berhasil" {{ $status === 'Berhasil' ? 'selected' : '' }}>
                                🟢 Berhasil
                            </option>
                            <option value="Gagal" {{ $status === 'Gagal' ? 'selected' : '' }}>
                                🔴 Gagal
                            </option>
                            <option value="Pending" {{ $status === 'Pending' ? 'selected' : '' }}>
                                ⏳ Pending
                            </option>
                        </select>
                    </div>

                    <button type="submit" class="w-full flex justify-center items-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-all shadow-sm hover:shadow-md">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </form>

            </div>

        </div>

    </div>

</div>
