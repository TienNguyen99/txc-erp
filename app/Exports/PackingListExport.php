<?php

namespace App\Exports;

use App\Exports\Sheets\PackingListDetailSheet;
use App\Exports\Sheets\PackingListSummarySheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PackingListExport implements WithMultipleSheets
{
    use Exportable;

    protected string $trackingNumber;

    public function __construct(string $trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;
    }

    public function sheets(): array
    {
        return [
            new PackingListDetailSheet($this->trackingNumber),
            new PackingListSummarySheet($this->trackingNumber),
        ];
    }
}
