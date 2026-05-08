@extends('layouts.app')
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title mb-0"><i class="fa-solid fa-bell me-2"></i>Thông Báo Hệ Thống</h4>
        <button class="btn btn-outline-secondary btn-sm" id="btnMarkAll">
            <i class="fa-solid fa-check-double me-1"></i>Đánh dấu tất cả đã đọc
        </button>
    </div>
    <div class="card-page">
        @include('admin.partials.alert')
        @forelse($notifications as $n)
        <div class="d-flex align-items-start gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }} {{ $n->is_read ? 'opacity-60' : '' }}"
            style="{{ !$n->is_read ? 'background:#fefce8;margin:-1px;padding:1rem 1.75rem;border-radius:8px;' : '' }}">
            <span class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                style="width:38px;height:38px;background:var(--bg);border:1.5px solid var(--border)">
                <i class="fa-solid {{ $n->icon }} text-{{ $n->type }}"></i>
            </span>
            <div class="flex-grow-1">
                <div class="fw-semibold" style="font-size:.875rem">{{ $n->title }}</div>
                @if($n->message)
                <div class="text-muted" style="font-size:.8rem">{{ $n->message }}</div>
                @endif
                <div class="text-muted" style="font-size:.75rem">{{ $n->created_at->diffForHumans() }}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($n->link)
                <a href="{{ $n->link }}" class="btn btn-outline-primary btn-xs">Xem</a>
                @endif
                @if(!$n->is_read)
                <button class="btn btn-outline-secondary btn-xs btn-read" data-id="{{ $n->id }}">✓</button>
                @endif
                <form method="POST" action="{{ route('admin.notifications.destroy', $n) }}" class="d-inline"
                    onsubmit="return confirm('Xóa?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-xs"><i class="fa-solid fa-times"></i></button>
                </form>
            </div>
        </div>
        @empty
        <p class="text-muted text-center py-4">Không có thông báo nào.</p>
        @endforelse
        <div class="mt-3">{{ $notifications->links() }}</div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    document.querySelectorAll('.btn-read').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch('{{ route("admin.notifications.mark-read") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ ids: [id] })
            }).then(() => location.reload());
        });
    });

    document.getElementById('btnMarkAll')?.addEventListener('click', function() {
        fetch('{{ route("admin.notifications.mark-read") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ ids: 'all' })
        }).then(() => location.reload());
    });
</script>
@endsection
