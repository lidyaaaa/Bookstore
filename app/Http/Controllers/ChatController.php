<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Halaman percakapan / chat.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $activeUser = null;
        $activeTransaction = null;

        if ($request->filled('transaction_id')) {
            $activeTransaction = Transaction::with(['user', 'items.book'])->find($request->transaction_id);
            if ($activeTransaction) {
                if ($currentUser->role === 'admin') {
                    $activeUser = $activeTransaction->user;
                } else {
                    $activeUser = User::where('role', 'admin')->first();
                }
            }
        }

        if (! $activeUser && $request->filled('user_id')) {
            $candidate = User::find($request->user_id);
            if ($currentUser->role === 'admin') {
                $activeUser = $candidate;
            } elseif ($candidate && $candidate->role === 'admin') {
                $activeUser = $candidate;
            }
        }

        if ($currentUser->role === 'admin') {
            // Daftar kontak untuk admin: semua user pelanggan yang pernah chat atau bertransaksi
            $contactIds = Message::where('sender_id', $currentUser->id)
                ->orWhere('receiver_id', $currentUser->id)
                ->pluck('sender_id')
                ->merge(
                    Message::where('sender_id', $currentUser->id)
                        ->orWhere('receiver_id', $currentUser->id)
                        ->pluck('receiver_id')
                )
                ->merge(Transaction::pluck('user_id'))
                ->unique()
                ->reject(fn ($id) => $id == $currentUser->id);

            $contacts = User::whereIn('id', $contactIds)
                ->withCount([
                    'sentMessages as unread_count' => function ($q) use ($currentUser): void {
                        $q->where('receiver_id', $currentUser->id)->where('is_read', false);
                    },
                ])
                ->latest()
                ->get();

            if (! $activeUser && $contacts->isNotEmpty()) {
                $activeUser = $contacts->first();
            }
        } else {
            // Customer: lawan bicaranya adalah admin toko
            $admin = User::where('role', 'admin')->first();
            $contacts = $admin ? collect([$admin]) : collect();
            if (! $activeUser) {
                $activeUser = $admin;
            }
        }

        $messages = collect();
        if ($activeUser) {
            // Tandai pesan dari activeUser ke currentUser sebagai sudah dibaca
            Message::where('sender_id', $activeUser->id)
                ->where('receiver_id', $currentUser->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $messages = Message::where(function ($q) use ($currentUser, $activeUser): void {
                $q->where('sender_id', $currentUser->id)->where('receiver_id', $activeUser->id);
            })->orWhere(function ($q) use ($currentUser, $activeUser): void {
                $q->where('sender_id', $activeUser->id)->where('receiver_id', $currentUser->id);
            })
                ->with(['sender', 'transaction'])
                ->oldest()
                ->get();
        }

        // Ambil riwayat transaksi antara activeUser dan admin jika ada
        $userTransactions = collect();
        if ($currentUser->role === 'admin' && $activeUser) {
            $userTransactions = Transaction::where('user_id', $activeUser->id)
                ->with('items.book')
                ->latest()
                ->take(5)
                ->get();
        } elseif ($currentUser->role !== 'admin') {
            $userTransactions = Transaction::where('user_id', $currentUser->id)
                ->with('items.book')
                ->latest()
                ->take(5)
                ->get();
        }

        return view('chat.index', compact('contacts', 'activeUser', 'messages', 'activeTransaction', 'userTransactions'));
    }

    /**
     * Kirim pesan chat.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'transaction_id' => 'nullable|exists:transactions,id',
            'message' => 'required|string|max:2000',
        ]);

        $currentUser = Auth::user();

        // Customer hanya boleh chat dengan admin, admin boleh chat dengan siapapun
        $receiver = User::findOrFail($validated['receiver_id']);
        if ($currentUser->role !== 'admin' && $receiver->role !== 'admin') {
            abort(403, 'Anda hanya dapat mengirim pesan ke admin toko.');
        }

        Message::create([
            'sender_id' => $currentUser->id,
            'receiver_id' => $receiver->id,
            'transaction_id' => $validated['transaction_id'] ?? null,
            'message' => trim($validated['message']),
            'is_read' => false,
        ]);

        return redirect()->route('chat.index', [
            'user_id' => $receiver->id,
            'transaction_id' => $validated['transaction_id'] ?? null,
        ])->with('success', 'Pesan berhasil dikirim.');
    }
}
