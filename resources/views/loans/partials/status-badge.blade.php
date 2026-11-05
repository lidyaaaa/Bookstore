@php
    $pillStyle = match($loan->status) {
        'menunggu' => 'bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50',
        'dipinjam' => $loan->isOverdue()
            ? 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25'
            : 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
        'dikembalikan' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
        'hilang' => 'bg-danger text-white',
        default => 'bg-secondary bg-opacity-10 text-slate-700 border',
    };
@endphp

<span class="badge {{ $pillStyle }}">
    {{ $loan->isOverdue() ? 'Menunggak (Overdue)' : ucfirst($loan->status) }}
</span>

@if($loan->late_days > 0)
    <div class="small text-danger fw-bold mt-1" style="font-size: 0.76rem;">
        Telat {{ $loan->late_days }} hr
        &rarr; Suspend {{ $loan->hari_sanksi > 0 ? $loan->hari_sanksi : \App\Models\Loan::calculateSanctionDays($loan->late_days) }} hr
    </div>
@elseif($loan->status === 'hilang')
    <div class="small text-danger fw-bold mt-1" style="font-size: 0.76rem;">
        Hilang &rarr; Suspend 30 hr
    </div>
@endif
