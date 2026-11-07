<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TahunAjaran;
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
            'name' => 'required|string|max:225',
            'email' => 'required|email|max:225|unique:tb_users,email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $name = $request->input('name');
        $email = $request->input('email');

        // Buat username unik berdasarkan email / nama
        $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0]));
        $username = $baseUsername ?: 'user_' . time();
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter++;
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

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
