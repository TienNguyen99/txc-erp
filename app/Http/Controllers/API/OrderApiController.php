<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 50), 200);

        return OrderResource::collection(Order::query()->latest()->paginate($perPage));
    }

    public function show($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        return new OrderResource($order);
    }

    public function store(Request $request)
    {
        $order = Order::create($this->validatedData($request));
        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        $order->update($this->validatedData($request, $order->id));
        return new OrderResource($order);
    }

    public function destroy($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        $order->delete();
        return response()->json(['message' => 'Order deleted']);
    }

    private function validatedData(Request $request, ?int $orderId = null): array
    {
        return $request->validate([
            'khach_hang_id' => ['nullable', 'exists:danh_muc_khach_hang,id'],
            'nhan_vien_id' => ['nullable', 'exists:users,id'],
            'chart' => ['nullable', 'string', 'max:255'],
            'job_no' => [$orderId ? 'sometimes' : 'required', 'string', 'max:255'],
            'fty_po' => ['nullable', 'string', 'max:255'],
            'im_number' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'qty' => ['nullable', 'numeric'],
            'unit' => ['nullable', 'string', 'max:50'],
            'ma_hh' => ['nullable', 'string', 'max:255'],
            'quy_cach' => ['nullable', 'string', 'max:255'],
            'ten_hh' => ['nullable', 'string', 'max:255'],
            'kich_co' => ['nullable', 'string', 'max:255'],
            'yrd' => ['nullable', 'numeric'],
            'can_giao_1' => ['nullable', 'numeric'],
            'can_giao_2' => ['nullable', 'numeric'],
            'pl_number' => ['nullable', 'string', 'max:255'],
            'tagtime_etc' => ['nullable', 'date'],
            'sig_need_date' => ['nullable', 'date'],
            'noi_giao' => ['nullable', 'string', 'max:255'],
            'price_usd_auto' => ['nullable', 'numeric'],
            'price_usd' => ['nullable', 'numeric'],
            'to_khai' => ['nullable', 'string'],
            'lenh_sanxuat' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:pending,in_production,done,shipped'],
        ]);
    }
}
