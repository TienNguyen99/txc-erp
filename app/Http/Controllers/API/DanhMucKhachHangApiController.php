<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DanhMucKhachHang;
use Illuminate\Http\Request;
use App\Http\Resources\DanhMucKhachHangResource;

class DanhMucKhachHangApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 50), 200);

        return DanhMucKhachHangResource::collection(DanhMucKhachHang::query()->orderBy('ma_kh')->paginate($perPage));
    }

    public function show($id)
    {
        $item = DanhMucKhachHang::find($id);
        if (!$item) {
            return response()->json(['message' => 'DanhMucKhachHang not found'], 404);
        }
        return new DanhMucKhachHangResource($item);
    }

    public function store(Request $request)
    {
        $item = DanhMucKhachHang::create($this->validatedData($request));
        return (new DanhMucKhachHangResource($item))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        $item = DanhMucKhachHang::find($id);
        if (!$item) {
            return response()->json(['message' => 'DanhMucKhachHang not found'], 404);
        }
        $item->update($this->validatedData($request, true));
        return new DanhMucKhachHangResource($item);
    }

    public function destroy($id)
    {
        $item = DanhMucKhachHang::find($id);
        if (!$item) {
            return response()->json(['message' => 'DanhMucKhachHang not found'], 404);
        }
        $item->delete();
        return response()->json(['message' => 'DanhMucKhachHang deleted']);
    }

    private function validatedData(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'ma_kh' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'ten_kh' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'nguoi_lien_he' => ['nullable', 'string', 'max:255'],
            'sdt' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'dia_chi' => ['nullable', 'string'],
            'ma_so_thue' => ['nullable', 'string', 'max:255'],
            'ghi_chu' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ]);
    }
}
