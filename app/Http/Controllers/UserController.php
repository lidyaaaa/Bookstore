<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Menampilkan daftar seluruh pengguna/pelanggan yang terdaftar di sistem.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->search);
        $role = $request->role;

        $users = User::query()
            ->withCount(['transactions', 'carts'])
            ->withSum('transactions as total_belanja', 'total_harga')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nomor_anggota', 'like', "%{$search}%")
                        ->orWhere('telepon', 'like', "%{$search}%");
                });
            })
            ->when($role, function ($query, $role): void {
                $query->where('role', $role);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalMembers = User::where('role', 'user')->count();
        $totalAdmins = User::where('role', 'admin')->count();

        return view('admin.users.index', compact('users', 'totalMembers', 'totalAdmins'));
    }
}
