<div class="mt-2 pt-1">
    @if($book->stok > 0)
        <div class="d-flex gap-1">
            <form action="{{ route('cart.store', $book) }}" method="POST" class="flex-grow-1">
                @csrf
                <input type="hidden" name="jumlah" value="1">
                <button type="submit" class="btn btn-primary btn-sm w-100 py-2">
                    <i class="bi bi-cart-plus-fill"></i> + Keranjang
                </button>
            </form>
        </div>
    @else
        <button class="btn btn-soft-secondary btn-sm w-100 py-2 opacity-75" disabled>
            <i class="bi bi-x-circle"></i> Stok Habis
        </button>
    @endif
</div>
