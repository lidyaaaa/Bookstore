<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nisn' => 'required|digits:10|unique:users,nisn',
            'email' => 'required|email|max:255|unique:users,email',
            'kelas_jabatan' => ['required', Rule::in(User::KELAS_OPTIONS)],
            'telepon' => 'required|digits_between:10,13',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'nisn.digits' => 'NISN harus berupa 10 digit angka.',
            'nisn.unique' => 'NISN sudah terdaftar.',
            'kelas_jabatan.in' => 'Pilih kelas yang tersedia.',
            'telepon.digits_between' => 'Nomor telepon harus angka 10–13 digit.',
        ]);

        User::create([
            'name' => $data['name'],
            'nisn' => $data['nisn'],
            'email' => $data['email'],
            'kelas_jabatan' => $data['kelas_jabatan'],
            'telepon' => $data['telepon'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Registrasi keanggotaan berhasil. Silakan login.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withErrors([
                'email' => 'Email atau password salah.',
            ])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Anda berhasil logout.');
    }
}
