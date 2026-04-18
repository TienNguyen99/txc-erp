<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all();
        // If settings table is empty, create defaults
        if ($settings->isEmpty()) {
            Setting::insert([
                ['key' => 'company_name', 'value' => 'TXC Garment', 'description' => 'Tên công ty xuất báo cáo', 'type' => 'string'],
                ['key' => 'default_wastage_percent', 'value' => '3', 'description' => 'Tỷ lệ hao hụt mặc định (%)', 'type' => 'number'],
                ['key' => 'enable_qc_module', 'value' => '0', 'description' => 'Bật module Quality Control (QC)', 'type' => 'boolean'],
            ]);
            $settings = Setting::all();
        }

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = $request->except(['_token', '_method']);

        foreach ($settings as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Cập nhật cấu hình thành công!');
    }
}
