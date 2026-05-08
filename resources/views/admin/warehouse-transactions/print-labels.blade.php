@extends('layouts.app')

@section('title', 'In Tem Dán Thùng - ' . $tn)

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">In Tem Dán Thùng (Tracking: {{ $tn }})</h6>
            <a href="{{ route('admin.warehouse-transactions.index', ['tracking_filter' => $tn]) }}" class="btn btn-secondary btn-sm">Quay lại</a>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <strong>Hướng dẫn:</strong> Hệ thống đã tự động tính toán <b>{{ $totalCartons }}</b> thùng dựa trên định mức.<br>
                Bạn hãy tích chọn các thùng muốn in (nên chọn 4 thùng 1 lần để vừa 1 tờ giấy A4).<br>
                Bạn có thể sửa lại <b>N/WEIGHT</b> và <b>G/WEIGHT</b> thực tế sau khi cân trước khi ấn IN.
            </div>

            <form action="{{ route('admin.warehouse-transactions.render-labels') }}" method="POST" target="_blank" id="printForm">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="50" class="text-center">
                                    <input type="checkbox" id="checkAll" class="form-check-input" checked>
                                </th>
                                <th width="100">Carton No</th>
                                <th>Item Code</th>
                                <th>Màu (Color)</th>
                                <th>Job / PO</th>
                                <th width="100">Số lượng</th>
                                <th width="120">N.W (kgs)</th>
                                <th width="120">G.W (kgs)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cartonsData as $i => $c)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="labels[{{ $i }}][selected]" value="1" class="form-check-input check-item" checked>
                                </td>
                                <td>
                                    <input type="hidden" name="labels[{{ $i }}][json]" value="{{ json_encode($c) }}">
                                    <strong>{{ $c['carton_no'] }} / {{ $c['total_cartons'] }}</strong>
                                </td>
                                <td>{{ $c['item_code'] }}</td>
                                <td>{{ $c['color'] }}</td>
                                <td><span class="badge bg-warning text-dark">{{ $c['job'] }}</span><br><small>{{ $c['po'] }}</small></td>
                                <td class="text-end fw-bold">{{ $c['qty'] }}</td>
                                <td>
                                    <input type="number" step="0.01" name="labels[{{ $i }}][nw]" value="{{ $c['nw'] }}" class="form-control form-control-sm text-end">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="labels[{{ $i }}][gw]" value="{{ $c['gw'] }}" class="form-control form-control-sm text-end">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-print"></i> IN TEM ĐÃ CHỌN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('checkAll');
    const checkItems = document.querySelectorAll('.check-item');
    
    checkAll.addEventListener('change', function() {
        checkItems.forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('printForm').addEventListener('submit', function(e) {
        let checkedCount = 0;
        checkItems.forEach(cb => {
            if(cb.checked) checkedCount++;
        });
        if(checkedCount === 0) {
            e.preventDefault();
            alert('Vui lòng chọn ít nhất 1 thùng để in!');
        }
    });
});
</script>
@endsection
