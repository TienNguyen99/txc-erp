<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WarehouseDocument;
use App\Services\WarehouseDocumentService;
use Illuminate\Http\Request;

class WarehouseDocumentController extends Controller
{
    public function storeFromTransactions(Request $request, WarehouseDocumentService $documentService)
    {
        $validated = $request->validate([
            'transaction_ids' => ['required', 'array'],
            'transaction_ids.*' => ['exists:warehouse_transactions,id'],
        ]);

        $document = $documentService->createFromTransactions($validated['transaction_ids'], $request->user());

        return redirect()
            ->route('admin.warehouse-documents.show', $document)
            ->with('success', "Đã tạo phiếu kho {$document->document_no}.");
    }

    public function show(WarehouseDocument $warehouseDocument)
    {
        $warehouseDocument->load('items', 'createdBy');

        return view('admin.warehouse-documents.show', compact('warehouseDocument'));
    }

    public function print(WarehouseDocument $warehouseDocument)
    {
        $warehouseDocument->update(['printed_at' => now()]);
        $warehouseDocument->load('items', 'createdBy');

        return view('admin.warehouse-documents.print', compact('warehouseDocument'));
    }
}
