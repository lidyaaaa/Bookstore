<div class="d-flex gap-2 mt-2 pt-1">
    <a href="{{ route('books.edit', $book) }}" class="btn btn-soft-primary btn-sm flex-grow-1">
        <i class="bi bi-pencil-square"></i> Edit Buku
    </a>
    <form action="{{ route('books.destroy', $book) }}" method="POST"
        onsubmit="return confirm('Yakin ingin menghapus koleksi {{ addslashes($book->judul) }}?')">
        @csrf
        @method('DELETE')
        <button class="btn btn-soft-danger btn-sm px-2" title="Hapus Koleksi">
            <i class="bi bi-trash3"></i>
        </button>
    </form>
</div>
