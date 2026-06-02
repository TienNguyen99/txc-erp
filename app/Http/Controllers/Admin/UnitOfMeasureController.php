<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitOfMeasureController extends Controller
{
    public function index()
    {
        $units = UnitOfMeasure::orderBy('dimension')->orderBy('code')->get();

        return view('admin.units-of-measure.index', compact('units'));
    }

    public function store(Request $request)
    {
        UnitOfMeasure::create($this->validated($request));

        return back()->with('success', 'Đã thêm đơn vị tính.');
    }

    public function update(Request $request, UnitOfMeasure $unitOfMeasure)
    {
        $unitOfMeasure->update($this->validated($request, $unitOfMeasure));

        return back()->with('success', 'Đã cập nhật đơn vị tính.');
    }

    private function validated(Request $request, ?UnitOfMeasure $unit = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('units_of_measure')->ignore($unit)],
            'name' => ['required', 'string', 'max:100'],
            'dimension' => ['required', Rule::in(['mass', 'length', 'quantity', 'volume', 'packaging'])],
            'factor_to_base' => ['required', 'numeric', 'min:0.000001'],
            'is_base' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
        ]);
        $validated['is_base'] = $request->boolean('is_base');
        $validated['active'] = $request->boolean('active');

        return $validated;
    }
}
