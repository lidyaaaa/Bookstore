@php
    $eligibility = auth()->user()->evaluateBorrowEligibility();
@endphp

@if(! $eligibility['allowed'])
    <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4"
        style="background: #fffbeb; border-left: 4px solid #f59e0b !important;">
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-danger bg-opacity-10 text-danger p-2 rounded-3">
                <i class="bi bi-shield-lock-fill fs-5"></i>
            </span>
            <div>
                <div class="fw-bold text-slate-900">Akses Peminjaman Dibatasi</div>
                <div class="small text-slate-700">{{ $eligibility['reason'] }}</div>
            </div>
        </div>
        <a href="{{ route('loans.index') }}" class="btn btn-danger btn-sm">
            Lihat Pinjaman Saya <i class="bi bi-arrow-right"></i>
        </a>
    </div>
@endif
