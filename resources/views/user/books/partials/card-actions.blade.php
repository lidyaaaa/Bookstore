@php
    $canUserBorrow = auth()->user()->evaluateBorrowEligibility()['allowed'];
    $isBorrowedByUser = $isBorrowedByUser ?? auth()->user()->activeLoans()->where('book_id', $book->id)->exists();
@endphp

<div class="mt-2 pt-1">
    @if($book->stok > 0 && $canUserBorrow && ! $isBorrowedByUser)
        <button type="button" class="btn btn-soft-primary btn-sm w-100 py-2"
            data-bs-toggle="modal" data-bs-target="#bookDetailModal-{{ $book->id }}">
            <i class="bi bi-bookmark-plus-fill"></i> Pinjam
        </button>
    @elseif($book->stok < 1 && ! $isBorrowedByUser)
        <button class="btn btn-soft-secondary btn-sm w-100 py-2 opacity-75" disabled>
            Sedang Dipinjam
        </button>
    @else
        <button class="btn btn-soft-secondary btn-sm w-100 py-2 opacity-75" disabled
            title="Buku sedang Anda pinjam atau akun terkunci">
            <i class="bi bi-lock-fill"></i> Pinjam
        </button>
    @endif
</div>
