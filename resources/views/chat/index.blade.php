@extends('layouts.app')

@section('title', 'Live Chat Bookstore — BookStore')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <div>
                <h3 class="fw-bold mb-1">
                    <i class="bi bi-chat-dots-fill text-primary me-2"></i>
                    {{ auth()->user()->role === 'admin' ? 'Pusat Pesan & Chat Pelanggan' : 'Chat Customer Service / Admin' }}
                </h3>
                <p class="text-secondary small mb-0">
                    {{ auth()->user()->role === 'admin' 
                        ? 'Komunikasi langsung dengan pelanggan terkait pemesanan, ketersediaan buku, dan pengiriman.' 
                        : 'Hubungi admin toko langsung melalui web untuk menanyakan buku atau update pesanan Anda.' }}
                </p>
            </div>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-receipt me-1"></i> Lihat Daftar Transaksi
                </a>
            @else
                <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-receipt me-1"></i> Riwayat Pesanan Saya
                </a>
            @endif
        </div>
    </div>

    {{-- Kolom Kontak (Hanya Admin yang melihat list kontak user) --}}
    @if(auth()->user()->role === 'admin')
        <div class="col-lg-4 col-md-5">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-3">
                    <div class="fw-bold text-slate-800">
                        <i class="bi bi-people-fill text-primary me-1"></i> Kontak Pelanggan ({{ $contacts->count() }})
                    </div>
                </div>
                <div class="list-group list-group-flush overflow-y-auto" style="max-height: 580px;">
                    @forelse($contacts as $contact)
                        <a href="{{ route('chat.index', ['user_id' => $contact->id]) }}"
                           class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 {{ $activeUser && $activeUser->id === $contact->id ? 'active bg-primary bg-opacity-10 text-slate-900 border-start border-4 border-primary' : '' }}">
                            <div class="d-flex align-items-center gap-2 text-truncate">
                                <span class="avatar-chip flex-shrink-0" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                    {{ strtoupper(substr($contact->name, 0, 1)) }}
                                </span>
                                <div class="text-truncate">
                                    <div class="fw-bold text-truncate" style="font-size: 0.9rem;">{{ $contact->name }}</div>
                                    <div class="small text-muted font-monospace" style="font-size: 0.75rem;">
                                        {{ $contact->nomor_anggota ?? 'Pelanggan' }}
                                    </div>
                                </div>
                            </div>
                            @if($contact->unread_count > 0)
                                <span class="badge rounded-pill bg-danger ms-2">{{ $contact->unread_count }}</span>
                            @endif
                        </a>
                    @empty
                        <div class="p-4 text-center text-muted small">
                            <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                            Belum ada percakapan atau pelanggan yang terdaftar.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- Kolom Chat Box --}}
    <div class="{{ auth()->user()->role === 'admin' ? 'col-lg-8 col-md-7' : 'col-lg-9 col-md-10 mx-auto' }}">
        <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column overflow-hidden">
            {{-- Header Chat Box --}}
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                @if($activeUser)
                    <div class="d-flex align-items-center gap-3">
                        <span class="avatar-chip" style="width: 42px; height: 42px; font-size: 1rem;">
                            {{ strtoupper(substr($activeUser->name, 0, 1)) }}
                        </span>
                        <div>
                            <div class="fw-bold text-slate-900 mb-0">
                                {{ $activeUser->name }}
                                @if($activeUser->role === 'admin')
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 ms-1" style="font-size: 0.7rem;">Admin Resmi</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 ms-1" style="font-size: 0.7rem;">Pelanggan</span>
                                @endif
                            </div>
                            <div class="small text-muted font-monospace" style="font-size: 0.75rem;">
                                {{ $activeUser->nomor_anggota ?? $activeUser->email }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="fw-semibold text-secondary">
                        <i class="bi bi-chat me-1"></i> Percakapan
                    </div>
                @endif

                @if($activeTransaction)
                    <span class="badge bg-light text-primary border border-primary border-opacity-25 px-3 py-2 font-monospace">
                        <i class="bi bi-tag-fill me-1"></i> Terkait Pesanan: {{ $activeTransaction->kode_transaksi }}
                    </span>
                @endif
            </div>

            {{-- Info Bar Transaksi Terkait (Jika ada) --}}
            @if($activeTransaction)
                <div class="bg-primary bg-opacity-10 border-bottom px-4 py-2 d-flex justify-content-between align-items-center small">
                    <div>
                        <strong>Pesanan {{ $activeTransaction->kode_transaksi }}</strong> &mdash; 
                        Total: <strong>Rp {{ number_format($activeTransaction->total_harga, 0, ',', '.') }}</strong> 
                        ({{ $activeTransaction->items->count() }} judul buku)
                        <span class="badge bg-secondary bg-opacity-25 text-dark ms-1">{{ strtoupper($activeTransaction->status) }}</span>
                    </div>
                    <a href="{{ route('transactions.receipt', $activeTransaction) }}" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 0.75rem;" target="_blank">
                        Lihat Struk <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>
                </div>
            @endif

            {{-- Area Pesan Chat --}}
            <div class="card-body p-4 overflow-y-auto flex-grow-1" id="chatMessagesContainer" style="min-height: 400px; max-height: 550px; background-color: #f8fafc;">
                @if(!$activeUser)
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-chat-square-dots fs-1 d-block mb-2 text-primary opacity-50"></i>
                        <p class="mb-0">Pilih kontak pelanggan untuk memulai percakapan.</p>
                    </div>
                @elseif($messages->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-chat-heart fs-1 d-block mb-2 text-primary opacity-50"></i>
                        <p class="fw-semibold mb-1">Belum ada riwayat pesan.</p>
                        <p class="small mb-0">Kirim pesan pertama Anda di bawah ini untuk memulai percakapan!</p>
                    </div>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach($messages as $msg)
                            @php
                                $isMe = $msg->sender_id === auth()->id();
                            @endphp
                            <div class="d-flex {{ $isMe ? 'justify-content-end' : 'justify-content-start' }}">
                                <div class="d-flex flex-column {{ $isMe ? 'align-items-end' : 'align-items-start' }}" style="max-width: 75%;">
                                    <div class="small text-muted mb-1 px-1" style="font-size: 0.72rem;">
                                        {{ $isMe ? 'Anda' : $msg->sender->name }} &bull; {{ $msg->created_at->format('H:i') }}
                                    </div>
                                    <div class="p-3 rounded-4 shadow-sm {{ $isMe ? 'bg-primary text-white' : 'bg-white text-slate-800 border' }}" style="word-break: break-word;">
                                        @if($msg->transaction_id && (!$activeTransaction || $activeTransaction->id !== $msg->transaction_id))
                                            <div class="mb-1 pb-1 border-bottom {{ $isMe ? 'border-white-50 text-white-50' : 'border-light text-muted' }}" style="font-size: 0.72rem;">
                                                <i class="bi bi-receipt me-1"></i> Pesanan: <strong>{{ $msg->transaction?->kode_transaksi }}</strong>
                                            </div>
                                        @endif
                                        <div style="white-space: pre-wrap; font-size: 0.92rem;">{{ $msg->message }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Form Input Pesan --}}
            @if($activeUser)
                <div class="card-footer bg-white border-top p-3">
                    <form action="{{ route('chat.store') }}" method="POST" class="d-flex flex-column gap-2">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $activeUser->id }}">
                        @if($activeTransaction)
                            <input type="hidden" name="transaction_id" value="{{ $activeTransaction->id }}">
                        @endif

                        <div class="input-group">
                            <textarea name="message" class="form-control" rows="2" placeholder="Ketik pesan Anda di sini..." required style="resize: none;"></textarea>
                            <button class="btn btn-primary px-4 fw-semibold" type="submit">
                                <i class="bi bi-send-fill me-1"></i> Kirim
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const container = document.getElementById("chatMessagesContainer");
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>
@endsection
