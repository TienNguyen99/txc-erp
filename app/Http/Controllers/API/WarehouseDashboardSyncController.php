<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\WarehouseInventoryDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseDashboardSyncController extends Controller
{
    public function show(Request $request, WarehouseInventoryDashboardService $dashboardService): JsonResponse
    {
        if (! $this->authenticate($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'thang' => ['nullable', 'integer', 'between:1,12'],
            'nam' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $month = (int) ($validated['thang'] ?? now()->month);
        $year = (int) ($validated['nam'] ?? now()->year);
        $sheet = $dashboardService->sheetValues($month, $year);

        return response()->json([
            'ok' => true,
            'month' => $month,
            'year' => $year,
            'generated_at' => now()->toDateTimeString(),
            ...$sheet,
        ]);
    }

    private function authenticate(Request $request): bool
    {
        $token = Setting::where('key', 'api_sync_token')->value('value');

        if (empty($token)) {
            return false;
        }

        $bearer = $request->bearerToken();

        return $bearer && hash_equals($token, $bearer);
    }
}
