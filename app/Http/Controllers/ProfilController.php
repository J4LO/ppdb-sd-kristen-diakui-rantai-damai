<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    public function admin()
    {
        $user = auth()->user();

        return view('profil.admin', compact('user'));
    }

    public function panitia()
    {
        $user = auth()->user(); // Mendapatkan user yang sedang login
        $panitia = $user->panitia; // Mendapatkan data panitia dari user yang sedang login

        return view('profil.panitia', compact('panitia'));
    }

    public function casis()
    {
        $user = auth()->user(); // Mendapatkan user yang sedang login
        $casis = $user->casis; // Mendapatkan data calon siswa (casis) dari user yang sedang login
        $archivedCasis = $casis ? null : $user->calonSiswa()->onlyTrashed()->first();
        $isArchived = !empty($archivedCasis);

        return view('profil.casis', compact('casis', 'user', 'archivedCasis', 'isArchived'));
    }

    /**
     * Memperbarui data akun orang tua (nama, email, nomor HP).
     * Melakukan sinkronisasi dua arah ke tb_calon_siswa.no_hp_orang_tua.
     */
    public function updateProfil(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Normalisasi nomor HP sebelum validasi
        $rawHp = $request->input('no_hp');
        $normalizedHp = null;
        if (!is_null($rawHp) && trim($rawHp) !== '') {
            $digits = preg_replace('/[^0-9]/', '', $rawHp);
            if (str_starts_with($digits, '62')) {
                $digits = '0' . substr($digits, 2);
            }
            if (!str_starts_with($digits, '0') && str_starts_with($digits, '8')) {
                $digits = '0' . $digits;
            }
            $normalizedHp = $digits;
        }
        $request->merge(['no_hp' => $normalizedHp]);

        // Validasi input
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('tb_users', 'email')->ignore($user->id_user, 'id_user'),
            ],
            'no_hp' => ['nullable', 'regex:/^08[0-9]{8,13}$/'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'no_hp.regex' => 'Format nomor HP tidak valid. Gunakan format nomor Indonesia (contoh: 081234567890).',
        ]);

        $newEmail = strtolower(trim($request->input('email')));
        $isEmailChanged = ($newEmail !== strtolower(trim($user->email)));

        // Jika email diubah, minta konfirmasi kata sandi saat ini
        if ($isEmailChanged) {
            $request->validate([
                'password_saat_ini' => ['required'],
            ], [
                'password_saat_ini.required' => 'Masukkan kata sandi saat ini untuk mengonfirmasi perubahan email.',
            ]);

            if (!Hash::check($request->input('password_saat_ini'), $user->password)) {
                return back()
                    ->withErrors(['password_saat_ini' => 'Kata sandi saat ini tidak cocok.'])
                    ->withInput();
            }

            // Catat log perubahan email tanpa data sensitif (masked)
            $oldParts = explode('@', $user->email);
            $local = $oldParts[0] ?? '';
            $domain = $oldParts[1] ?? '';
            $maskedLocal = strlen($local) > 2 ? substr($local, 0, 1) . '***' . substr($local, -1) : substr($local, 0, 1) . '***';
            $maskedEmail = $maskedLocal . '@' . $domain;

            Log::info("Perubahan email akun (id_user: {$user->id_user}) dari email lama {$maskedEmail}.");
        }

        // Simpan data dalam database transaction
        DB::transaction(function () use ($user, $validated, $newEmail, $normalizedHp) {
            // Whitelist: hanya nama, email, no_hp pada tb_users
            $user->nama = $validated['nama'];
            $user->email = $newEmail;
            $user->no_hp = $normalizedHp;
            $user->save();

            // Sinkronisasi nomor HP ke tb_calon_siswa milik user ini
            // Kolom selain no_hp_orang_tua TIDAK disentuh
            DB::table('tb_calon_siswa')
                ->where('id_user', $user->id_user)
                ->update(['no_hp_orang_tua' => $normalizedHp]);
        });

        return redirect()->route('casis')->with('success', 'Data akun berhasil diperbarui.');
    }

    /**
     * Mengganti kata sandi akun pengguna.
     */
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Validasi input
        $request->validate([
            'password_lama' => ['required'],
            'password_baru' => ['required', 'string', 'min:8', 'confirmed', 'different:password_lama'],
        ], [
            'password_lama.required' => 'Kata sandi saat ini wajib diisi.',
            'password_baru.required' => 'Kata sandi baru wajib diisi.',
            'password_baru.min' => 'Kata sandi baru minimal 8 karakter.',
            'password_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password_baru.different' => 'Kata sandi baru harus berbeda dengan kata sandi saat ini.',
        ]);

        // Verifikasi kata sandi lama
        if (!Hash::check($request->input('password_lama'), $user->password)) {
            return back()
                ->withErrors(['password_lama' => 'Kata sandi saat ini tidak cocok.'])
                ->withInput();
        }

        // Simpan kata sandi baru yang telah di-hash
        $user->password = Hash::make($request->input('password_baru'));
        $user->save();

        // Regenerasi session setelah ganti password
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return redirect()->route('casis')->with('success', 'Kata sandi berhasil diubah.');
    }
}
