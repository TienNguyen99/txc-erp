<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DanhMucHangHoaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ten_hang' => $this->ten_hang,
            'ma_hh' => $this->ma_hh,
            'don_vi' => $this->don_vi,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
