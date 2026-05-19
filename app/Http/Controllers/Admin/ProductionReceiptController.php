<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductionReceipt;

class ProductionReceiptController extends Controller
{
    public function show(ProductionReceipt $productionReceipt)
    {
        $productionReceipt->load('items', 'postedBy');

        return view('admin.production-receipts.show', compact('productionReceipt'));
    }

    public function print(ProductionReceipt $productionReceipt)
    {
        $productionReceipt->update(['printed_at' => now()]);
        $productionReceipt->load('items', 'postedBy');

        return view('admin.production-receipts.print', compact('productionReceipt'));
    }
}
