<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DanhMucHangHoa;
use Illuminate\Http\Request;
use App\Http\Resources\DanhMucHangHoaResource;

class DanhMucHangHoaApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 50), 200);

        return DanhMucHangHoaResource::collection(DanhMucHangHoa::query()->orderBy('ma_hh')->paginate($perPage));
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
        $item = DanhMucHangHoa::create($this->validatedData($request));
        return (new DanhMucHangHoaResource($item))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        $item = DanhMucHangHoa::find($id);
        if (!$item) {
            return response()->json(['message' => 'DanhMucHangHoa not found'], 404);
        }
        $item->update($this->validatedData($request, true));
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

    private function validatedData(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'ma_hh' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'ten_hh' => ['nullable', 'string', 'max:255'],
            'mau' => ['nullable', 'string', 'max:255'],
            'kich_co' => ['nullable', 'string', 'max:255'],
            'nhom_hh' => ['nullable', 'string', 'max:255'],
            'don_vi' => ['nullable', 'string', 'max:50'],
            'don_gia' => ['nullable', 'numeric'],
            'quy_cach' => ['nullable', 'string'],
            'yards_per_roll' => ['nullable', 'numeric'],
            'rolls_per_carton' => ['nullable', 'integer'],
            'dinh_muc_thung' => ['nullable', 'integer'],
            'net_weight' => ['nullable', 'numeric'],
            'gross_weight' => ['nullable', 'numeric'],
            'hinh_anh' => ['nullable', 'string'],
            'mo_ta' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
            'nha_cung_cap_id' => ['nullable', 'exists:nha_cung_cap,id'],
            'gia_nvl' => ['nullable', 'numeric'],
            'ton_toi_thieu' => ['nullable', 'integer'],
        ]);
    }
}
