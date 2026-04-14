<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseTransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'ma_hh' => $this->ma_hh,
            'so_luong' => $this->so_luong,
            'loai' => $this->loai,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
