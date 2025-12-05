<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * Hiển thị tất cả setting
     */
    public function index()
    {
        $settings = Setting::orderBy('key')->get();
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Form chỉnh sửa nhiều setting cùng lúc
     */
    public function edit()
    {
        $settings = Setting::orderBy('key')->get();
        return view('admin.settings.edit', compact('settings'));
    }

    /**
     * Cập nhật nhiều setting
     */
    public function update(Request $request)
    {
        $input = $request->except('_token');

        DB::transaction(function () use ($input) {
            foreach ($input as $key => $value) {
                $setting = Setting::find($key);
                if (!$setting) continue;

                // Validate giá trị theo type/rules
                $setting->validateValue($value);

                $setting->value = $value;
                $setting->save();

                Log::info("Cập nhật Setting: {$key} = {$value}");
            }
        });

        return redirect()->route('admin.settings.index')->with('success', 'Cập nhật cấu hình thành công.');
    }
}
