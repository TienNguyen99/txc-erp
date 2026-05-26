<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;
use App\Http\Resources\WarehouseTransactionResource;

class WarehouseTransactionApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 50), 200);

        return WarehouseTransactionResource::collection(WarehouseTransaction::query()->latest()->paginate($perPage));
    }

    public function show($id)
    {
        $item = WarehouseTransaction::find($id);
        if (!$item) {
            return response()->json(['message' => 'WarehouseTransaction not found'], 404);
        }
        return new WarehouseTransactionResource($item);
    }

    public function store(Request $request)
    {
        $item = WarehouseTransaction::create($this->validatedData($request));
        return (new WarehouseTransactionResource($item))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        $item = WarehouseTransaction::find($id);
        if (!$item) {
            return response()->json(['message' => 'WarehouseTransaction not found'], 404);
        }
        $item->update($this->validatedData($request, true));
        return new WarehouseTransactionResource($item);
    }

    public function destroy($id)
    {
        $item = WarehouseTransaction::find($id);
        if (!$item) {
            return response()->json(['message' => 'WarehouseTransaction not found'], 404);
        }
        $item->delete();
        return response()->json(['message' => 'WarehouseTransaction deleted']);
    }

    private function validatedData(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'production_receipt_id' => ['nullable', 'exists:production_receipts,id'],
            'warehouse_document_id' => ['nullable', 'exists:warehouse_documents,id'],
            'cong_doan' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'ma_hh' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'hang_hoa_id' => ['nullable', 'exists:danh_muc_hang_hoa,id'],
            'ngay' => [$partial ? 'sometimes' : 'required', 'date'],
            'size' => ['nullable', 'string', 'max:255'],
            'mau' => ['nullable', 'string', 'max:255'],
            'so_luong' => [$partial ? 'sometimes' : 'required', 'numeric'],
            'price_usd' => ['nullable', 'numeric'],
            'exchange_rate' => ['nullable', 'numeric'],
            'ma_nv' => ['nullable', 'string', 'max:255'],
            'lenh_sx' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
        ]);
    }
}
