<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DanhMucKhachHangResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ten_khach_hang' => $this->ten_khach_hang,
            'ma_khach_hang' => $this->ma_khach_hang,
            'dia_chi' => $this->dia_chi,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
