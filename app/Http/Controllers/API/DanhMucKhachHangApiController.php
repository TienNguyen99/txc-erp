<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DanhMucKhachHang;
use Illuminate\Http\Request;
use App\Http\Resources\DanhMucKhachHangResource;

class DanhMucKhachHangApiController extends Controller
{
    public function index()
    {
        return DanhMucKhachHangResource::collection(DanhMucKhachHang::all());
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
        $item = DanhMucKhachHang::create($request->all());
        return (new DanhMucKhachHangResource($item))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        $item = DanhMucKhachHang::find($id);
        if (!$item) {
            return response()->json(['message' => 'DanhMucKhachHang not found'], 404);
        }
        $item->update($request->all());
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
}
