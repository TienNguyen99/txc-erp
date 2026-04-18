@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1"><i class="fa-solid fa-gears me-2"></i>Cấu hình hệ thống</h4>
            <p class="text-muted mb-0" style="font-size:.85rem">Quản lý các tham số hoạt động của xưởng</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card-page" style="max-width: 800px;">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row g-4">
                @foreach($settings as $setting)
                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size: .9rem">
                            {{ $setting->description ?: mb_convert_case(str_replace('_', ' ', $setting->key), MB_CASE_TITLE) }}
                            <span class="text-muted fw-normal ms-2" style="font-size: .8rem">({{ $setting->key }})</span>
                        </label>
                        
                        @if($setting->type === 'boolean')
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" role="switch" name="{{ $setting->key }}" value="1" {{ $setting->value == '1' ? 'checked' : '' }}>
                                <label class="form-check-label">{{ $setting->value == '1' ? 'Đang bật' : 'Đang tắt' }}</label>
                            </div>
                            <input type="hidden" name="{{ $setting->key }}" value="0" @if($setting->value == '1') disabled @endif>
                        @elseif($setting->type === 'number')
                            <input type="number" step="any" class="form-control" name="{{ $setting->key }}" value="{{ $setting->value }}">
                        @elseif($setting->type === 'text')
                            <textarea class="form-control" name="{{ $setting->key }}" rows="3">{{ $setting->value }}</textarea>
                        @else
                            <input type="text" class="form-control" name="{{ $setting->key }}" value="{{ $setting->value }}">
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-4 text-end border-top pt-3">
                <button type="submit" class="btn btn-primary px-4 rounded-3 shadow-sm">
                    <i class="fa-solid fa-save me-2"></i>Lưu cấu hình
                </button>
            </div>
        </form>
    </div>
</div>

@section('js')
<script>
    // Handle toggle switch hidden input logic
    document.querySelectorAll('.form-check-input').forEach(function(el) {
        el.addEventListener('change', function() {
            let hiddenInput = this.parentElement.nextElementSibling;
            if (this.checked) {
                hiddenInput.disabled = true;
                this.nextElementSibling.textContent = 'Đang bật';
            } else {
                hiddenInput.disabled = false;
                this.nextElementSibling.textContent = 'Đang tắt';
            }
        });
    });
</script>
@endsection
@endsection
