<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DanhMucHangHoa;
use Illuminate\Http\Request;
use App\Http\Resources\DanhMucHangHoaResource;

class DanhMucHangHoaApiController extends Controller
{
    public function index()
    {
        return DanhMucHangHoaResource::collection(DanhMucHangHoa::all());
    }

    public function show($id)
    {
        $item = DanhMucHangHoa::find($id);
        if (!$item) {
            return response()->json(['message' => 'DanhMucHangHoa not found'], 404);
        }
        return new DanhMucHangHoaResource($item);
    }

    public function store(Request $request)
    {
        $item = DanhMucHangHoa::create($request->all());
        return (new DanhMucHangHoaResource($item))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        $item = DanhMucHangHoa::find($id);
        if (!$item) {
            return response()->json(['message' => 'DanhMucHangHoa not found'], 404);
        }
        $item->update($request->all());
        return new DanhMucHangHoaResource($item);
    }

    public function destroy($id)
    {
        $item = DanhMucHangHoa::find($id);
        if (!$item) {
            return response()->json(['message' => 'DanhMucHangHoa not found'], 404);
        }
        $item->delete();
        return response()->json(['message' => 'DanhMucHangHoa deleted']);
    }
}
