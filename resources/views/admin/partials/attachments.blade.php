{{-- Partial: File Đính Kèm --}}
{{-- Params: $attachable_type, $attachable_id, $attachments (collection) --}}
<div id="attachments-section">
    @if(isset($attachments) && $attachments->count())
    <ul class="list-group list-group-flush mb-3" id="attachment-list">
        @foreach($attachments as $att)
        <li class="list-group-item px-0 d-flex align-items-center gap-2" style="font-size:.825rem">
            <i class="fa-solid {{ $att->is_image ? 'fa-image' : 'fa-file-alt' }} text-muted" style="width:16px"></i>
            <a href="{{ $att->url }}" target="_blank" class="text-truncate" style="max-width:280px">{{ $att->file_name }}</a>
            @if($att->label)
                <span class="badge bg-light text-dark border">{{ $att->label }}</span>
            @endif
            <span class="text-muted ms-auto">{{ $att->file_size_human }}</span>
            <form method="POST" action="{{ route('admin.attachments.destroy', $att) }}" class="d-inline"
                onsubmit="return confirm('Xóa file này?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-xs"><i class="fa-solid fa-times"></i></button>
            </form>
        </li>
        @endforeach
    </ul>
    @else
    <p class="text-muted small" id="no-attach-msg">Chưa có tài liệu đính kèm.</p>
    @endif

    <form id="upload-form" class="d-flex align-items-end gap-2 flex-wrap" style="font-size:.85rem">
        @csrf
        <input type="hidden" name="attachable_type" value="{{ $attachable_type }}">
        <input type="hidden" name="attachable_id" value="{{ $attachable_id }}">
        <div>
            <label class="form-label mb-1">Label (tuỳ chọn)</label>
            <input type="text" name="label" id="att-label" class="form-control form-control-sm" placeholder="VD: Hợp đồng" style="width:140px">
        </div>
        <div>
            <label class="form-label mb-1">Chọn file</label>
            <input type="file" id="att-file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.gif,.xlsx,.xls,.doc,.docx" style="width:220px">
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-upload">
            <i class="fa-solid fa-upload me-1"></i>Tải lên
        </button>
        <span id="upload-msg" class="text-muted small"></span>
    </form>
</div>

<script>
document.getElementById('btn-upload')?.addEventListener('click', function () {
    const file = document.getElementById('att-file').files[0];
    if (!file) return alert('Chọn file trước!');
    const formEl = document.getElementById('upload-form');
    const fd = new FormData(formEl);
    fd.append('file', file);
    const msg = document.getElementById('upload-msg');
    msg.textContent = 'Đang tải...';
    fetch('{{ route("admin.attachments.store") }}', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.id) {
                msg.textContent = '✓ Đã tải lên';
                // Thêm vào list
                const list = document.getElementById('attachment-list') ?? (() => {
                    const ul = document.createElement('ul');
                    ul.className = 'list-group list-group-flush mb-3';
                    ul.id = 'attachment-list';
                    document.getElementById('no-attach-msg')?.remove();
                    document.getElementById('attachments-section').prepend(ul);
                    return ul;
                })();
                const li = document.createElement('li');
                li.className = 'list-group-item px-0 d-flex align-items-center gap-2';
                li.style.fontSize = '.825rem';
                li.innerHTML = `<i class="fa-solid ${data.is_image ? 'fa-image' : 'fa-file-alt'} text-muted" style="width:16px"></i>
                    <a href="${data.url}" target="_blank" class="text-truncate" style="max-width:280px">${data.file_name}</a>
                    <span class="text-muted ms-auto">${data.size}</span>`;
                list.appendChild(li);
                document.getElementById('att-file').value = '';
                document.getElementById('att-label').value = '';
            }
        })
        .catch(() => { msg.textContent = 'Lỗi tải lên!'; });
});
</script>
