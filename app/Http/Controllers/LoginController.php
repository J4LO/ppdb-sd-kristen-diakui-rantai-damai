<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TahunAjaran;
use App\Models\CalonSiswa;
use App\Models\panitia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function registrasi(Request $request)
    {
        if ($request->input('captcha_failed')) {
            return response()->json([
                'message' => 'Verifikasi Captcha gagal. Silakan coba lagi.'
            ]);
        }

        $tahunAjarBerlangsung = TahunAjaran::where('status', 'Berlangsung')->first();

        if (!$tahunAjarBerlangsung) {
            return redirect()->back()->with('error', 'Pendaftaran Siswa Baru Telah Ditutup');
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:225',
            'username' => 'nullable|string|max:50|unique:tb_users,username|regex:/^[a-zA-Z0-9_.-]+$/',
            'email'    => 'required|email|max:225|unique:tb_users,email',
            'password' => 'required|min:6',
        ], [
            'name.required'   => 'Nama lengkap casis wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan, silakan gunakan username lain.',
            'username.regex'  => 'Username hanya boleh berupa kombinasi huruf, angka, strip (-), titik (.), atau underscore (_).',
            'email.required'  => 'Email aktif wajib diisi.',
            'email.email'     => 'Format email tidak valid.',
            'email.unique'    => 'Email ini sudah terdaftar dalam sistem.',
            'password.required' => 'Password wajib diisi.',
            'password.min'    => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $customUsername = trim($request->input('username', ''));

        // Jika username diisi manual, gunakan username tersebut; jika tidak, buatkan otomatis dari email
        if (!empty($customUsername)) {
            $username = $customUsername;
        } else {
            $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0]));
            $username = $baseUsername ?: 'user_' . time();
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter++;
            }
        }

        // Simpan data user dengan role 'Orang Tua'
        $user = User::create([
            'nama' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($request->input('password')),
            'role' => 'Orang Tua',
        ]);

        return redirect('/')->with('success', 'Registrasi berhasil! Silakan login untuk melanjutkan pendaftaran.');
    }

    public function loginproses(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
            'captcha' => 'required|numeric',
        ]);

        $validator->after(function ($validator) use ($request) {
            $num1 = intval($request->input('num1'));
            $num2 = intval($request->input('num2'));
            $captcha = intval($request->input('captcha'));

            if ($captcha !== ($num1 + $num2)) {
                $validator->errors()->add('captcha', 'Jawaban Captcha salah. Silakan coba lagi.');
            }
        });

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorMessages = [];

            if ($errors->has('email')) {
                $errorMessages[] = 'Email / Username wajib diisi.';
            }
            if ($errors->has('password')) {
                $errorMessages[] = 'Password harus diisi.';
            }
            if ($errors->has('captcha')) {
                $errorMessages[] = $errors->first('captcha');
            }

            $errorMessage = implode(' ', $errorMessages);

            return redirect('/')
                ->withErrors($validator)
                ->withInput($request->except('password'))
                ->with('error', $errorMessage ?: 'Mohon isi semua field dengan benar.');
        }

        // Mendukung login via Email maupun Username
        $loginInput = $request->input('email');
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $field => $loginInput,
            'password' => $request->input('password'),
        ];

        // Attempt login
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Simpan objek user di session
            $request->session()->put('user', $user);

            // Redirect berdasarkan role pengguna
            switch ($user->role) {
                case 'Admin':
                case 'Kepala Sekolah':
                    return redirect('/admin/dashboard')->with('success', 'Selamat datang, ' . ($user->nama ?: $user->name));
                case 'Panitia':
                    return redirect('/beranda/panitia')->with('success', 'Selamat datang, ' . ($user->nama ?: $user->name));
                case 'Orang Tua':
                case 'Calon Siswa':
                    return redirect('/beranda/casis')->with('success', 'Selamat datang, ' . ($user->nama ?: $user->name));
                default:
                    Auth::logout();
                    return redirect('/')->with('error', 'Akses ditolak. Hubungi administrator untuk informasi lebih lanjut.');
            }
        }

        // Login gagal
        return redirect('/')
            ->withInput($request->except('password'))
            ->with('error', 'Email/Username atau password salah. Silakan coba lagi.');
    }

    public function resetPasswordPublic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_reset' => 'required',
            'no_hp_reset' => 'required',
            'password'    => 'required|min:6|confirmed',
            'captcha'     => 'required|numeric',
        ], [
            'email_reset.required' => 'Email atau username wajib diisi.',
            'no_hp_reset.required' => 'Nomor HP terdaftar wajib diisi.',
            'password.required'    => 'Password baru wajib diisi.',
            'password.min'         => 'Password baru minimal 6 karakter.',
            'password.confirmed'   => 'Konfirmasi password baru tidak cocok.',
            'captcha.required'     => 'Verifikasi keamanan wajib diisi.',
            'captcha.numeric'      => 'Jawaban verifikasi keamanan harus berupa angka.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $num1 = intval($request->input('num1'));
            $num2 = intval($request->input('num2'));
            $captcha = intval($request->input('captcha'));

            if ($captcha !== ($num1 + $num2)) {
                $validator->errors()->add('captcha', 'Jawaban verifikasi keamanan salah. Silakan coba lagi.');
            }
        });

        if ($validator->fails()) {
            return redirect('/')
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('forgot_error', 'Harap periksa kembali beberapa inputan berikut.');
        }

        $identity = trim($request->input('email_reset'));
        $inputDigits = preg_replace('/\D/', '', $request->input('no_hp_reset'));

        // Cari user berdasarkan email atau username
        $user = User::where('email', $identity)
            ->orWhere('username', $identity)
            ->first();

        if (!$user) {
            return redirect('/')
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('forgot_error', 'Akun dengan Email atau Username tersebut tidak ditemukan.');
        }

        // Verifikasi kecocokan nomor HP atau identitas
        $matched = false;

        // 1. Cek langsung di kolom no_hp pada tb_users
        if (!empty($user->no_hp)) {
            $userDigits = preg_replace('/\D/', '', $user->no_hp);
            if ($userDigits === $inputDigits || str_ends_with($userDigits, $inputDigits) || str_ends_with($inputDigits, $userDigits)) {
                $matched = true;
            }
        }

        // 2. Jika Orang Tua, cek di tb_calon_siswa (no_hp_orang_tua atau nik_calon_siswa)
        if (!$matched && $user->role === 'Orang Tua') {
            $casis = CalonSiswa::where('id_user', $user->id_user)
                ->where(function ($q) use ($inputDigits) {
                    $q->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(no_hp_orang_tua, ' ', ''), '-', ''), '+', ''), '.', '') LIKE ?", ["%{$inputDigits}%"])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(nik_calon_siswa, ' ', ''), '-', ''), '+', ''), '.', '') LIKE ?", ["%{$inputDigits}%"]);
                })
                ->first();
            if ($casis) {
                $matched = true;
            }
        }

        // 3. Jika Panitia, cek di tb_panitia
        if (!$matched && $user->role === 'Panitia') {
            $panitiaUser = panitia::where('id_user', $user->id_user)
                ->where(function ($q) use ($inputDigits) {
                    $q->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(no_hp, ' ', ''), '-', ''), '+', ''), '.', '') LIKE ?", ["%{$inputDigits}%"]);
                })
                ->first();
            if ($panitiaUser) {
                $matched = true;
            }
        }

        // Jika nomor HP / NIK tidak cocok
        if (!$matched) {
            return redirect('/')
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('forgot_error', 'Nomor HP atau identitas verifikasi tidak sesuai dengan data akun terdaftar. Silakan hubungi Panitia PPDB jika Anda membutuhkan bantuan.');
        }

        // Perbarui password
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return redirect('/')
            ->with('success', 'Password akun Anda berhasil diperbarui! Silakan masuk menggunakan password baru.')
            ->with('login_modal', true);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
