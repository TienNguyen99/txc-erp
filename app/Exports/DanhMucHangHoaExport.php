<?php

namespace App\Exports;

use App\Models\DanhMucHangHoa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DanhMucHangHoaExport implements FromCollection, WithHeadings, WithMapping
{
    private $columns;

    public function __construct()
    {
        $this->columns = array_values(array_diff((new DanhMucHangHoa)->getFillable(), ['hinh_anh']));
    }

    public function collection()
    {
        return DanhMucHangHoa::orderBy('ma_hh')->get();
    }

    public function headings(): array
    {
        return $this->columns;
    }

    public function map($row): array
    {
        return collect($this->columns)->map(fn($col) => $row->$col)->toArray();
    }
}
