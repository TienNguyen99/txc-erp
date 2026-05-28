<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'chart' => $this->chart,
            'nhan_vien_id' => $this->nhan_vien_id,
            'khach_hang_id' => $this->khach_hang_id,
            'ma_hh' => $this->ma_hh,
            'quy_cach' => $this->quy_cach,
            'ten_hh' => $this->ten_hh,
            'kich_co' => $this->kich_co,
            'color' => $this->color,
            'unit' => $this->unit,
            'yrd' => $this->yrd,
            'tagtime_etc' => $this->tagtime_etc,
            'sig_need_date' => $this->sig_need_date,
            'noi_giao' => $this->noi_giao,
            'job_no' => $this->job_no,
            'fty_po' => $this->fty_po,
            'im_number' => $this->im_number,
            'qty' => $this->qty,
            'can_giao_1' => $this->can_giao_1,
            'can_giao_2' => $this->can_giao_2,
            'pl_number' => $this->pl_number,
            'price_usd_auto' => $this->price_usd_auto,
            'price_usd' => $this->price_usd,
            'to_khai' => $this->to_khai,
            'lenh_sanxuat' => $this->lenh_sanxuat,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
