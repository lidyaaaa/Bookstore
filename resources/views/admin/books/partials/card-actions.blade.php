@php
    $inAnyCart = ($book->carts_count ?? $book->carts()->count()) > 0;
@endphp

<div class="d-flex gap-2 mt-2 pt-1">
    <a href="{{ route('books.edit', $book) }}" class="btn btn-soft-primary btn-sm flex-grow-1">
        <i class="bi bi-pencil-square"></i> Edit Buku
    </a>
    @if($inAnyCart)
        <form action="{{ route('books.destroy', $book) }}" method="POST"
            onsubmit="alert('Buku tidak dapat dihapus karena sedang berada di keranjang belanja pengguna!'); return false;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-soft-secondary btn-sm px-2"
                title="Tidak bisa dihapus: sedang ada di keranjang user">
                <i class="bi bi-lock-fill text-warning"></i>
            </button>
        </form>
    @else
        <form action="{{ route('books.destroy', $book) }}" method="POST"
            onsubmit="return confirm('Yakin ingin menghapus buku {{ addslashes($book->judul) }}?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-soft-danger btn-sm px-2" title="Hapus Buku">
                <i class="bi bi-trash3"></i>
            </button>
        </form>
    @endif
</div>
