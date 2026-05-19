@extends('layouts.app')

@section('page-title', 'AI Assistant')

@section('css')
    <style>
        .ai-shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 18px;
            min-height: calc(100vh - 140px);
        }

        .ai-chat {
            display: flex;
            flex-direction: column;
            min-height: 620px;
        }

        .ai-messages {
            flex: 1;
            overflow-y: auto;
            padding: 18px;
            background: #fffaf5;
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }

        .ai-message {
            max-width: 78%;
            margin-bottom: 12px;
            padding: 12px 14px;
            border-radius: 12px;
            white-space: pre-wrap;
            line-height: 1.55;
            font-size: .9rem;
        }

        .ai-message.user {
            margin-left: auto;
            background: var(--primary);
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .ai-message.assistant {
            background: #fff;
            border: 1px solid var(--border);
            color: var(--text);
            border-bottom-left-radius: 4px;
        }

        .ai-context-list {
            display: grid;
            gap: 10px;
        }

        .ai-context-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #f4eadf;
            padding-bottom: 9px;
            font-size: .86rem;
        }

        .ai-context-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .ai-context-value {
            font-weight: 700;
            color: var(--primary-dark);
            text-align: right;
        }

        .ai-suggestion {
            border: 1px solid var(--border);
            background: #fff;
            color: #475569;
            border-radius: 10px;
            padding: 9px 11px;
            text-align: left;
            font-size: .83rem;
            transition: all .15s;
        }

        .ai-suggestion:hover {
            border-color: var(--primary);
            color: var(--primary-dark);
            background: #fff8ef;
        }

        @media (max-width: 991px) {
            .ai-shell {
                grid-template-columns: 1fr;
            }

            .ai-chat {
                min-height: 560px;
            }

            .ai-message {
                max-width: 92%;
            }
        }
    </style>
@endsection

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="page-title mb-1"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>AI Assistant</h1>
            <div class="text-muted" style="font-size:.88rem">Trợ lý đọc dữ liệu ERP và gợi ý vận hành, không tự ghi dữ liệu.</div>
        </div>
    </div>

    <div class="ai-shell">
        <section class="card-page ai-chat">
            <div id="aiMessages" class="ai-messages">
                <div class="ai-message assistant">
Xin chào. Bạn có thể hỏi về đơn hàng, tồn kho, tracking, sản xuất hoặc mua hàng. Mình sẽ dùng dữ liệu ERP hiện tại để phân tích.
                </div>
            </div>

            <form id="aiForm" class="mt-3">
                @csrf
                <label for="aiMessage" class="form-label">Câu hỏi</label>
                <div class="d-flex gap-2">
                    <textarea id="aiMessage" class="form-control" rows="2" maxlength="2000"
                        placeholder="Ví dụ: Tóm tắt rủi ro vận hành hôm nay"></textarea>
                    <button id="aiSubmit" type="submit" class="btn btn-primary px-3" title="Gửi câu hỏi">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </section>

        <aside class="d-flex flex-column gap-3">
            <div class="card-page">
                <h2 class="mb-3" style="font-size:1rem;font-weight:700">Gợi ý nhanh</h2>
                <div class="d-grid gap-2">
                    <button class="ai-suggestion" type="button">Tóm tắt rủi ro vận hành hôm nay</button>
                    <button class="ai-suggestion" type="button">Mã hàng nào cần kiểm tra tồn kho?</button>
                    <button class="ai-suggestion" type="button">Đơn hàng/tracking nào nên ưu tiên xử lý?</button>
                    <button class="ai-suggestion" type="button">Tình hình sản xuất 30 ngày gần nhất ra sao?</button>
                </div>
            </div>

            <div class="card-page">
                <h2 class="mb-3" style="font-size:1rem;font-weight:700">Ngữ cảnh lần hỏi gần nhất</h2>
                <div id="aiContext" class="ai-context-list">
                    <div class="text-muted" style="font-size:.86rem">Chưa có dữ liệu hiển thị.</div>
                </div>
            </div>
        </aside>
    </div>
@endsection

@section('scripts')
    <script>
        const aiMessages = document.getElementById('aiMessages');
        const aiForm = document.getElementById('aiForm');
        const aiInput = document.getElementById('aiMessage');
        const aiSubmit = document.getElementById('aiSubmit');
        const aiContext = document.getElementById('aiContext');
        const history = [];

        function addMessage(role, content) {
            const bubble = document.createElement('div');
            bubble.className = `ai-message ${role}`;
            bubble.textContent = content;
            aiMessages.appendChild(bubble);
            aiMessages.scrollTop = aiMessages.scrollHeight;
        }

        function setLoading(isLoading) {
            aiSubmit.disabled = isLoading;
            aiInput.disabled = isLoading;
            aiSubmit.innerHTML = isLoading
                ? '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>'
                : '<i class="fa-solid fa-paper-plane"></i>';
        }

        function updateContext(context) {
            if (!context) return;

            const labels = {
                total_orders: 'Tổng đơn hàng',
                open_orders: 'Đơn đang mở',
                total_trackings: 'Tổng tracking',
                open_trackings: 'Tracking đang xử lý',
                warehouse_transactions_this_month: 'Giao dịch kho tháng này',
                open_production_orders: 'Lệnh SX đang mở',
                open_purchase_orders: 'PO đang mở'
            };

            aiContext.innerHTML = Object.entries(labels).map(([key, label]) => `
                <div class="ai-context-item">
                    <span>${label}</span>
                    <span class="ai-context-value">${context[key] ?? 0}</span>
                </div>
            `).join('');
        }

        async function askAi(message) {
            setLoading(true);

            try {
                const response = await fetch('{{ route('admin.ai-assistant.ask') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        message,
                        history: history.slice(-8)
                    })
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'Không gửi được câu hỏi.');
                }

                const suffix = payload.mode === 'local' ? '\n\n(Chế độ local: chưa cấu hình API key AI.)' : '';
                addMessage('assistant', payload.answer + suffix);
                updateContext(payload.context);

                history.push({ role: 'user', content: message });
                history.push({ role: 'assistant', content: payload.answer });
            } catch (error) {
                addMessage('assistant', error.message || 'Có lỗi khi gọi AI Assistant.');
            } finally {
                setLoading(false);
                aiInput.focus();
            }
        }

        aiForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const message = aiInput.value.trim();
            if (!message) return;

            addMessage('user', message);
            aiInput.value = '';
            askAi(message);
        });

        document.querySelectorAll('.ai-suggestion').forEach((button) => {
            button.addEventListener('click', () => {
                aiInput.value = button.textContent.trim();
                aiInput.focus();
            });
        });
    </script>
@endsection
