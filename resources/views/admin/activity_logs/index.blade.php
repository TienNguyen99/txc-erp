@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1"><i class="fa-solid fa-list-check me-2"></i>Nhật ký hoạt động</h4>
            <p class="text-muted mb-0" style="font-size:.85rem">Lịch sử thao tác trên hệ thống</p>
        </div>
    </div>

    <div class="card-page">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 15%">Thời gian</th>
                        <th style="width: 15%">Người thực hiện</th>
                        <th style="width: 15%">Hành động</th>
                        <th style="width: 20%">Subject</th>
                        <th style="width: 35%">Chi tiết thay đổi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $log->created_at->format('d/m/Y') }}</div>
                                <div class="text-muted" style="font-size:.75rem">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td>
                                @if($log->causer)
                                    <span class="badge bg-light text-dark border"><i class="fa-regular fa-user me-1"></i>{{ $log->causer->name }}</span>
                                @else
                                    <span class="text-muted">Hệ thống</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $colors = [
                                        'created' => 'success',
                                        'updated' => 'info',
                                        'deleted' => 'danger',
                                    ];
                                    $color = $colors[$log->event] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }} text-capitalize">{{ $log->event }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size:.85rem">{{ str_replace('App\\Models\\', '', $log->subject_type) }}</div>
                                <div class="text-muted" style="font-size:.75rem">ID: {{ $log->subject_id }}</div>
                            </td>
                            <td>
                                @if($log->properties && count($log->properties) > 0)
                                    <div class="bg-light p-2 rounded" style="font-size: .8rem; max-height: 100px; overflow-y: auto;">
                                        @if(isset($log->properties['old']) && isset($log->properties['attributes']))
                                            <table class="table table-sm table-bordered mb-0">
                                                <tr><th>Trường</th><th>Cũ</th><th>Mới</th></tr>
                                                @foreach($log->properties['attributes'] as $key => $value)
                                                    <tr>
                                                        <td class="fw-semibold">{{ $key }}</td>
                                                        <td class="text-danger"><del>{{ $log->properties['old'][$key] ?? '' }}</del></td>
                                                        <td class="text-success">{{ is_array($value) ? json_encode($value) : $value }}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        @else
                                            <pre class="mb-0"><code>{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted fst-italic" style="font-size: .8rem">Không có chi tiết</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fa-regular fa-folder-open mb-2" style="font-size: 2rem;"></i>
                                <p class="mb-0">Chưa có nhật ký hoạt động nào</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
