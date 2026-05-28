@extends('layouts.app')

@section('content')
@php
    $categoryIcons = [
        'data' => 'fa-database',
        'warehouse' => 'fa-boxes-stacked',
        'delivery' => 'fa-truck-fast',
        'system' => 'fa-gear',
    ];
    $categoryColors = [
        'data' => 'warning',
        'warehouse' => 'primary',
        'delivery' => 'success',
        'system' => 'secondary',
    ];
    $statusColors = [
        'open' => 'danger',
        'done' => 'success',
        'ignored' => 'secondary',
    ];
    $categoryTotal = fn($key) => (int) ($categoryCounts[$key] ?? 0);
    $statusTotal = fn($key) => (int) ($statusCounts[$key] ?? 0);
@endphp

<div class="container-fluid px-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="page-title mb-1"><i class="fa-solid fa-bell me-2"></i>Thông báo & việc cần xử lý</h4>
            <div class="text-muted small">Theo dõi dữ liệu thiếu, kho, giao hàng/VAT và cảnh báo hệ thống.</div>
        </div>
        <button class="btn btn-outline-secondary btn-sm" id="btnMarkAll">
            <i class="fa-solid fa-check-double me-1"></i>Đánh dấu đã đọc
        </button>
    </div>

    <div class="card-page mb-3">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.notifications.index', ['category' => 'all', 'status' => $status]) }}"
                class="btn btn-sm {{ $category === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                Tất cả
                <span class="badge bg-light text-dark ms-1">{{ $categoryCounts->sum() }}</span>
            </a>
            @foreach($categoryLabels as $key => $label)
                <a href="{{ route('admin.notifications.index', ['category' => $key, 'status' => $status]) }}"
                    class="btn btn-sm {{ $category === $key ? 'btn-primary' : 'btn-outline-secondary' }}">
                    <i class="fa-solid {{ $categoryIcons[$key] ?? 'fa-bell' }} me-1"></i>{{ $label }}
                    <span class="badge bg-light text-dark ms-1">{{ $categoryTotal($key) }}</span>
                </a>
            @endforeach
        </div>

        <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
            <a href="{{ route('admin.notifications.index', ['category' => $category, 'status' => 'open']) }}"
                class="btn btn-sm {{ $status === 'open' ? 'btn-danger' : 'btn-outline-danger' }}">
                Cần xử lý <span class="badge bg-light text-dark ms-1">{{ $statusTotal('open') }}</span>
            </a>
            <a href="{{ route('admin.notifications.index', ['category' => $category, 'status' => 'done']) }}"
                class="btn btn-sm {{ $status === 'done' ? 'btn-success' : 'btn-outline-success' }}">
                Đã xử lý <span class="badge bg-light text-dark ms-1">{{ $statusTotal('done') }}</span>
            </a>
            <a href="{{ route('admin.notifications.index', ['category' => $category, 'status' => 'ignored']) }}"
                class="btn btn-sm {{ $status === 'ignored' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                Bỏ qua <span class="badge bg-light text-dark ms-1">{{ $statusTotal('ignored') }}</span>
            </a>
            <a href="{{ route('admin.notifications.index', ['category' => $category, 'status' => 'all']) }}"
                class="btn btn-sm {{ $status === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">
                Mọi trạng thái
            </a>
        </div>
    </div>

    <div class="card-page">
        @include('admin.partials.alert')

        @forelse($notifications as $n)
            @php
                $cat = $n->category ?: 'system';
                $rowMuted = $n->status !== 'open' ? 'opacity-75' : '';
            @endphp
            <div class="d-flex align-items-start gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }} {{ $rowMuted }}"
                style="{{ !$n->is_read && $n->status === 'open' ? 'background:#fff7ed;margin:-1px;padding:1rem 1.75rem;border-radius:8px;' : '' }}">
                <span class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                    style="width:40px;height:40px;background:var(--bg);border:1.5px solid var(--border)">
                    <i class="fa-solid {{ $n->icon }} text-{{ $n->type }}"></i>
                </span>

                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="fw-semibold" style="font-size:.9rem">{{ $n->title }}</span>
                        <span class="badge bg-{{ $categoryColors[$cat] ?? 'secondary' }}">
                            {{ $categoryLabels[$cat] ?? 'Hệ thống' }}
                        </span>
                        <span class="badge bg-{{ $statusColors[$n->status] ?? 'secondary' }}">
                            {{ $statusLabels[$n->status] ?? $n->status }}
                        </span>
                    </div>
                    @if($n->message)
                        <div class="text-muted" style="font-size:.82rem">{{ $n->message }}</div>
                    @endif
                    <div class="text-muted mt-1" style="font-size:.75rem">
                        {{ $n->created_at->diffForHumans() }}
                        @if($n->resolved_at)
                            <span class="mx-1">•</span>Hoàn tất {{ $n->resolved_at->diffForHumans() }}
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-end align-items-center gap-2">
                    @if($n->link)
                        <a href="{{ $n->link }}" class="btn btn-outline-primary btn-xs">Xem</a>
                    @endif

                    @if($n->status === 'open')
                        <form method="POST" action="{{ route('admin.notifications.status', $n) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="done">
                            <button class="btn btn-success btn-xs">Xử lý xong</button>
                        </form>
                        <form method="POST" action="{{ route('admin.notifications.status', $n) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="ignored">
                            <button class="btn btn-outline-secondary btn-xs">Bỏ qua</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.notifications.status', $n) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="open">
                            <button class="btn btn-outline-danger btn-xs">Mở lại</button>
                        </form>
                    @endif

                    @if(!$n->is_read)
                        <button class="btn btn-outline-secondary btn-xs btn-read" data-id="{{ $n->id }}">Đã đọc</button>
                    @endif

                    <form method="POST" action="{{ route('admin.notifications.destroy', $n) }}" class="d-inline"
                        onsubmit="return confirm('Xóa thông báo này?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-xs"><i class="fa-solid fa-times"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-muted text-center py-4">Không có thông báo nào trong nhóm này.</p>
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
