<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function store(Request $request)
    {
        UserAddress::ensureTableExists();

        $data = $request->validate([
            'label' => 'required|string|max:50',
            'penerima' => 'required|string|max:100',
            'telepon' => 'required|string|max:30',
            'alamat_lengkap' => 'required|string|max:500',
        ]);

        $user = auth()->user();
        $hasExisting = UserAddress::where('user_id', $user->id)->exists();

        UserAddress::create([
            'user_id' => $user->id,
            'label' => trim($data['label']),
            'penerima' => trim($data['penerima']),
            'telepon' => trim($data['telepon']),
            'alamat_lengkap' => trim($data['alamat_lengkap']),
            'is_utama' => ! $hasExisting,
        ]);

        return back()->with('success', 'Alamat pengiriman baru berhasil ditambahkan.');
    }

    public function update(Request $request, UserAddress $address)
    {
        UserAddress::ensureTableExists();
        abort_unless($address->user_id === auth()->id(), 403);

        $data = $request->validate([
            'label' => 'required|string|max:50',
            'penerima' => 'required|string|max:100',
            'telepon' => 'required|string|max:30',
            'alamat_lengkap' => 'required|string|max:500',
            'is_utama' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_utama')) {
            UserAddress::where('user_id', auth()->id())->update(['is_utama' => false]);
        }

        $address->update([
            'label' => trim($data['label']),
            'penerima' => trim($data['penerima']),
            'telepon' => trim($data['telepon']),
            'alamat_lengkap' => trim($data['alamat_lengkap']),
            'is_utama' => $request->boolean('is_utama', $address->is_utama),
        ]);

        return back()->with('success', 'Alamat pengiriman berhasil diperbarui.');
    }

    public function destroy(UserAddress $address)
    {
        UserAddress::ensureTableExists();
        abort_unless($address->user_id === auth()->id(), 403);

        $address->delete();

        return back()->with('success', 'Alamat pengiriman berhasil dihapus.');
    }
}
