@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/jerosoler/Drawflow/dist/drawflow.min.css">
<style>
    .drawflow-wrapper {
        display: flex;
        height: 600px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }
    .drawflow-sidebar {
        width: 250px;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
        padding: 15px;
        overflow-y: auto;
    }
    .drawflow-canvas-container {
        flex: 1;
        position: relative;
    }
    #drawflow {
        width: 100%;
        height: 100%;
        background: #fdfdfd;
        background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
        background-size: 20px 20px;
    }
    .drag-drawflow {
        padding: 12px 15px;
        margin-bottom: 10px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        cursor: grab;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        color: #334155;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: all 0.2s;
    }
    .drag-drawflow:hover {
        border-color: #6366f1;
        box-shadow: 0 4px 6px -1px rgba(99,102,241,0.1);
    }
    
    /* Drawflow Node Styles */
    .drawflow .drawflow-node {
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        padding: 0;
        width: 200px;
    }
    .drawflow .drawflow-node.selected {
        border: 2px solid #6366f1;
    }
    .drawflow .drawflow-node .title-box {
        background: #f1f5f9;
        border-bottom: 1px solid #cbd5e1;
        padding: 10px 15px;
        border-radius: 8px 8px 0 0;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .drawflow .drawflow-node .box {
        padding: 15px;
        font-size: 13px;
        color: #64748b;
    }
    .drawflow .connection .main-path {
        stroke: #94a3b8;
        stroke-width: 3px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1"><i class="fa-solid fa-diagram-project me-2"></i>{{ isset($quyTrinh) ? 'Chỉnh sửa Quy trình' : 'Thêm mới Quy trình' }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.quy-trinh-san-xuat.index') }}">Quy trình Sản xuất</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ isset($quyTrinh) ? 'Sửa' : 'Thêm' }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <button type="button" class="btn btn-outline-danger me-2" onclick="editor.clearModuleSelected()">
                <i class="fa-solid fa-trash-can me-1"></i>Xóa sạch Canvas
            </button>
            <button type="button" class="btn btn-primary" onclick="submitForm()">
                <i class="fa-solid fa-floppy-disk me-1"></i>Lưu Quy trình
            </button>
        </div>
    </div>

    <form id="quyTrinhForm" action="{{ isset($quyTrinh) ? route('admin.quy-trinh-san-xuat.update', $quyTrinh->id) : route('admin.quy-trinh-san-xuat.store') }}" method="POST">
        @csrf
        @if(isset($quyTrinh))
            @method('PUT')
        @endif
        
        <input type="hidden" name="flow_data" id="flow_data" value="">
        
        <div class="card card-page border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Mã Quy trình <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="ma_quy_trinh" value="{{ old('ma_quy_trinh', $quyTrinh->ma_quy_trinh ?? '') }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Tên Quy trình <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="ten_quy_trinh" value="{{ old('ten_quy_trinh', $quyTrinh->ten_quy_trinh ?? '') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ngày hiệu lực</label>
                        <input type="date" class="form-control" name="ngay_hieu_luc" value="{{ old('ngay_hieu_luc', isset($quyTrinh) && $quyTrinh->ngay_hieu_luc ? $quyTrinh->ngay_hieu_luc->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="trang_thai">
                            <option value="active" {{ old('trang_thai', $quyTrinh->trang_thai ?? '') == 'active' ? 'selected' : '' }}>Đang sử dụng</option>
                            <option value="inactive" {{ old('trang_thai', $quyTrinh->trang_thai ?? '') == 'inactive' ? 'selected' : '' }}>Ngừng sử dụng</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Sản phẩm áp dụng (Tùy chọn)</label>
                        <select class="form-select" name="san_pham_ap_dung[]" multiple id="san_pham_select">
                            @php
                                $selectedProducts = old('san_pham_ap_dung', $quyTrinh->san_pham_ap_dung ?? []);
                                if(!is_array($selectedProducts)) $selectedProducts = [];
                            @endphp
                            @foreach($products as $p)
                                <option value="{{ $p->ma_hh }}" {{ in_array($p->ma_hh, $selectedProducts) ? 'selected' : '' }}>{{ $p->ma_hh }} - {{ $p->ten_hh }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-page border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 pt-3">
                <h6 class="fw-bold mb-0">Thiết kế Quy trình (Kéo thả công đoạn vào khung bên phải)</h6>
            </div>
            <div class="card-body">
                <div class="drawflow-wrapper">
                    <div class="drawflow-sidebar">
                        <h6 class="text-muted mb-3" style="font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Danh sách Công đoạn</h6>
                        
                        <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="det">
                            <i class="fa-solid fa-layer-group text-primary"></i><span> Dệt</span>
                        </div>
                        <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="dinh_hinh">
                            <i class="fa-solid fa-temperature-high text-danger"></i><span> Định hình</span>
                        </div>
                        <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="in_nhuoc">
                            <i class="fa-solid fa-droplet text-info"></i><span> In/Nhuộm</span>
                        </div>
                        <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="cat">
                            <i class="fa-solid fa-scissors text-warning"></i><span> Cắt</span>
                        </div>
                        <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="kcs">
                            <i class="fa-solid fa-magnifying-glass-chart text-success"></i><span> Kiểm tra (KCS)</span>
                        </div>
                        <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="dong_goi">
                            <i class="fa-solid fa-box-open text-secondary"></i><span> Đóng gói</span>
                        </div>
                        <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="nhap_kho">
                            <i class="fa-solid fa-warehouse text-dark"></i><span> Nhập kho</span>
                        </div>
                    </div>
                    <div class="drawflow-canvas-container">
                        <div id="drawflow" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/gh/jerosoler/Drawflow/dist/drawflow.min.js"></script>
<script>
    var id = document.getElementById("drawflow");
    const editor = new Drawflow(id);
    editor.reroute = true;
    editor.start();

    // Data init if editing
    @if(isset($quyTrinh) && $quyTrinh->flow_data)
        const flowData = @json($quyTrinh->flow_data);
        editor.import(flowData);
    @endif

    // Drag & Drop logic
    var mobile_item_selec = '';
    var mobile_last_move = null;

    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drag(ev) {
        if (ev.type === "touchstart") {
            mobile_item_selec = ev.target.closest(".drag-drawflow").getAttribute('data-node');
        } else {
            ev.dataTransfer.setData("node", ev.target.getAttribute('data-node'));
        }
    }

    function drop(ev) {
        if (ev.type === "touchend") {
            let parentdrawflow = document.elementFromPoint(mobile_last_move.touches[0].clientX, mobile_last_move.touches[0].clientY).closest("#drawflow");
            if(parentdrawflow != null) {
                addNodeToDrawFlow(mobile_item_selec, mobile_last_move.touches[0].clientX, mobile_last_move.touches[0].clientY);
            }
            mobile_item_selec = '';
        } else {
            ev.preventDefault();
            let data = ev.dataTransfer.getData("node");
            addNodeToDrawFlow(data, ev.clientX, ev.clientY);
        }
    }

    function addNodeToDrawFlow(name, pos_x, pos_y) {
        if(editor.editor_mode === 'fixed') {
            return false;
        }
        pos_x = pos_x * (editor.precanvas.clientWidth / (editor.precanvas.clientWidth * editor.zoom)) - (editor.precanvas.getBoundingClientRect().x * (editor.precanvas.clientWidth / (editor.precanvas.clientWidth * editor.zoom)));
        pos_y = pos_y * (editor.precanvas.clientHeight / (editor.precanvas.clientHeight * editor.zoom)) - (editor.precanvas.getBoundingClientRect().y * (editor.precanvas.clientHeight / (editor.precanvas.clientHeight * editor.zoom)));

        let html = "";
        let title = "";
        let icon = "";
        let nodeClass = name;
        
        // Define node content
        switch (name) {
            case 'det':
                title = "Dệt";
                icon = "<i class='fa-solid fa-layer-group text-primary'></i>";
                html = `<div><div class="title-box">${icon} ${title}</div><div class="box">Công đoạn Dệt</div></div>`;
                editor.addNode('det', 1, 1, pos_x, pos_y, 'det', {}, html);
                break;
            case 'dinh_hinh':
                title = "Định hình";
                icon = "<i class='fa-solid fa-temperature-high text-danger'></i>";
                html = `<div><div class="title-box">${icon} ${title}</div><div class="box">Công đoạn Định hình</div></div>`;
                editor.addNode('dinh_hinh', 1, 1, pos_x, pos_y, 'dinh_hinh', {}, html);
                break;
            case 'in_nhuoc':
                title = "In/Nhuộm";
                icon = "<i class='fa-solid fa-droplet text-info'></i>";
                html = `<div><div class="title-box">${icon} ${title}</div><div class="box">Công đoạn In/Nhuộm</div></div>`;
                editor.addNode('in_nhuoc', 1, 1, pos_x, pos_y, 'in_nhuoc', {}, html);
                break;
            case 'cat':
                title = "Cắt";
                icon = "<i class='fa-solid fa-scissors text-warning'></i>";
                html = `<div><div class="title-box">${icon} ${title}</div><div class="box">Công đoạn Cắt</div></div>`;
                editor.addNode('cat', 1, 1, pos_x, pos_y, 'cat', {}, html);
                break;
            case 'kcs':
                title = "Kiểm tra (KCS)";
                icon = "<i class='fa-solid fa-magnifying-glass-chart text-success'></i>";
                html = `<div><div class="title-box">${icon} ${title}</div><div class="box">Kiểm tra chất lượng</div></div>`;
                editor.addNode('kcs', 1, 1, pos_x, pos_y, 'kcs', {}, html);
                break;
            case 'dong_goi':
                title = "Đóng gói";
                icon = "<i class='fa-solid fa-box-open text-secondary'></i>";
                html = `<div><div class="title-box">${icon} ${title}</div><div class="box">Công đoạn Đóng gói</div></div>`;
                editor.addNode('dong_goi', 1, 1, pos_x, pos_y, 'dong_goi', {}, html);
                break;
            case 'nhap_kho':
                title = "Nhập kho";
                icon = "<i class='fa-solid fa-warehouse text-dark'></i>";
                html = `<div><div class="title-box">${icon} ${title}</div><div class="box">Nhập kho thành phẩm</div></div>`;
                editor.addNode('nhap_kho', 1, 0, pos_x, pos_y, 'nhap_kho', {}, html); // 1 input, 0 output for the end node
                break;
        }
    }

    function submitForm() {
        const flowData = editor.export();
        document.getElementById('flow_data').value = JSON.stringify(flowData);
        document.getElementById('quyTrinhForm').submit();
    }
</script>
@endsection
