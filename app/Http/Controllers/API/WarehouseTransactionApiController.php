<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;
use App\Http\Resources\WarehouseTransactionResource;

class WarehouseTransactionApiController extends Controller
{
    public function index()
    {
        return WarehouseTransactionResource::collection(WarehouseTransaction::all());
    }

    public function show($id)
    {
        $item = WarehouseTransaction::find($id);
        if (!$item) {
            return response()->json(['message' => 'WarehouseTransaction not found'], 404);
        }
        return new WarehouseTransactionResource($item);
    }

    public function store(Request $request)
    {
        $item = WarehouseTransaction::create($request->all());
        return (new WarehouseTransactionResource($item))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        $item = WarehouseTransaction::find($id);
        if (!$item) {
            return response()->json(['message' => 'WarehouseTransaction not found'], 404);
        }
        $item->update($request->all());
        return new WarehouseTransactionResource($item);
    }

    public function destroy($id)
    {
        $item = WarehouseTransaction::find($id);
        if (!$item) {
            return response()->json(['message' => 'WarehouseTransaction not found'], 404);
        }
        $item->delete();
        return response()->json(['message' => 'WarehouseTransaction deleted']);
    }
}
