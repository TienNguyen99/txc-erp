<?php

namespace App\Services\Ai;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiAssistantService
{
    public function __construct(private readonly ErpContextService $contextService)
    {
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     * @return array{answer: string, mode: string, context: array<string, mixed>}
     */
    public function answer(string $message, array $history, ?User $user): array
    {
        $context = $this->contextService->build();

        if (! $this->isConfigured()) {
            return [
                'answer' => $this->fallbackAnswer($message, $context),
                'mode' => 'local',
                'context' => $context['summary'],
            ];
        }

        try {
            return [
                'answer' => $this->askProvider($message, $history, $context, $user),
                'mode' => 'ai',
                'context' => $context['summary'],
            ];
        } catch (Throwable $exception) {
            Log::warning('AI assistant request failed.', [
                'message' => $exception->getMessage(),
            ]);

            return [
                'answer' => $this->fallbackAnswer($message, $context)
                    . "\n\nLưu ý: chưa gọi được dịch vụ AI bên ngoài, nên đây là phản hồi dự phòng từ dữ liệu nội bộ.",
                'mode' => 'local',
                'context' => $context['summary'],
            ];
        }
    }

    private function isConfigured(): bool
    {
        return filled(config('services.ai_assistant.api_key'));
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     * @param array<string, mixed> $context
     */
    private function askProvider(string $message, array $history, array $context, ?User $user): string
    {
        $baseUrl = rtrim((string) config('services.ai_assistant.base_url'), '/');
        $model = (string) config('services.ai_assistant.model', 'gpt-4o-mini');

        $messages = [
            [
                'role' => 'system',
                'content' => $this->systemPrompt($context, $user),
            ],
        ];

        foreach (array_slice($history, -8) as $item) {
            $messages[] = [
                'role' => $item['role'],
                'content' => $item['content'],
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        $response = Http::withToken((string) config('services.ai_assistant.api_key'))
            ->acceptJson()
            ->timeout((int) config('services.ai_assistant.timeout', 30))
            ->post($baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.2,
                'max_tokens' => 900,
            ])
            ->throw()
            ->json();

        return trim((string) data_get($response, 'choices.0.message.content', 'Không nhận được phản hồi từ AI.'));
    }

    /**
     * @param array<string, mixed> $context
     */
    private function systemPrompt(array $context, ?User $user): string
    {
        $userName = $user?->name ?: 'người dùng';
        $jsonContext = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
Bạn là AI Assistant nội bộ của TXC ERP, hỗ trợ {$userName} quản lý đơn hàng, kho, sản xuất, mua hàng và dữ liệu vận hành.

Quy tắc:
- Trả lời bằng tiếng Việt, ngắn gọn, thực tế, ưu tiên số liệu trong ERP context.
- Không bịa dữ liệu ngoài context. Nếu thiếu dữ liệu, nói rõ cần kiểm tra thêm ở module nào.
- Chỉ tư vấn và phân tích. Không khẳng định đã tạo/sửa/xóa dữ liệu.
- Khi có rủi ro vận hành, nêu việc nên làm tiếp theo theo thứ tự ưu tiên.

ERP context hiện tại:
{$jsonContext}
PROMPT;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function fallbackAnswer(string $message, array $context): string
    {
        $summary = $context['summary'];
        $risks = $context['risks'];

        $lines = [
            'Mình đã nhận câu hỏi: "' . $message . '".',
            '',
            'Tóm tắt nhanh từ ERP hiện tại:',
            '- Đơn hàng đang mở: ' . $summary['open_orders'] . '/' . $summary['total_orders'],
            '- Tracking đang xử lý: ' . $summary['open_trackings'] . '/' . $summary['total_trackings'],
            '- Giao dịch kho tháng này: ' . $summary['warehouse_transactions_this_month'],
            '- Lệnh sản xuất đang mở: ' . $summary['open_production_orders'],
            '- PO đang mở: ' . $summary['open_purchase_orders'],
        ];

        if (! empty($risks)) {
            $lines[] = '';
            $lines[] = 'Điểm cần chú ý:';
            foreach ($risks as $risk) {
                $lines[] = '- ' . $risk;
            }
        }

        $lines[] = '';
        $lines[] = 'Để trả lời thông minh hơn theo câu hỏi tự nhiên, hãy cấu hình AI_ASSISTANT_API_KEY trong .env.';

        return implode("\n", $lines);
    }
}
