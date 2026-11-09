<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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
            'email' => 'required|email|max:255|unique:users,email',
            'telepon' => 'required|digits_between:10,13',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()->symbols()],
        ], [
            'telepon.digits_between' => 'Nomor telepon harus berupa angka 10–13 digit.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.letters' => 'Password harus mengandung minimal satu huruf.',
            'password.numbers' => 'Password harus mengandung minimal satu angka.',
            'password.symbols' => 'Password harus mengandung minimal satu simbol (contoh: @, #, !, $, _).',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'kelas_jabatan' => 'Pelanggan Umum',
            'telepon' => $data['telepon'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Registrasi akun berhasil. Silakan login untuk mulai berbelanja.');
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
            $request->session()->forget('url.intended');

            return redirect()->route('dashboard');
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
