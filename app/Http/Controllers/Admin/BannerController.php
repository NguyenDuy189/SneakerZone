<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    /**
     * Danh sách Banner
     * Hỗ trợ tìm kiếm, lọc, phân trang và sắp xếp
     */
    public function index(Request $request)
    {
        $query = Banner::query();

        // 1. Tìm kiếm (Search)
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where('title', 'like', "%{$keyword}%");
        }

        // 2. Lọc theo vị trí (Filter Position)
        if ($request->filled('position') && $request->position !== 'all') {
            $query->where('position', $request->position);
        }

        // 3. Lọc theo trạng thái (Filter Status)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        // 4. Sắp xếp: Ưu tiên (priority) lớn nhất lên đầu, sau đó đến mới nhất
        // Giả sử DB có cột 'priority' (integer, default 0)
        $banners = $query->orderByDesc('priority')
                         ->latest('id')
                         ->paginate(10)
                         ->appends($request->all());

        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Giao diện tạo mới
     */
    public function create()
    {
        return view('admin.banners.create');
    }

    /**
     * Xử lý lưu banner mới
     */
    public function store(Request $request)
    {
        // 1. Validate dữ liệu
        $validated = $this->validateRequest($request);

        DB::beginTransaction();
        try {
            // 2. Chuẩn bị dữ liệu
            $data = [
                'title'     => strip_tags($validated['title']),
                'link'      => $validated['link'],
                'position'  => $validated['position'],
                'priority'  => $request->input('priority', 0), // Mặc định 0 nếu không nhập
                'is_active' => $request->has('is_active'),     // Checkbox trả về true/false
                'content'   => $request->input('content'),     // Nội dung mô tả (nếu có)
            ];

            // 3. Xử lý upload ảnh
            if ($request->hasFile('image')) {
                $data['image_url'] = $this->uploadImage($request->file('image'));
            }

            // 4. Tạo bản ghi
            $banner = Banner::create($data);

            // 5. Ghi log hệ thống
            Log::info("Banner created by Admin ID: " . Auth::id(), ['banner_id' => $banner->id]);

            DB::commit();

            return redirect()->route('admin.banners.index')
                ->with('success', 'Thêm mới banner thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Xóa ảnh rác nếu lỡ upload mà DB lỗi
            if (isset($data['image_url'])) {
                $this->deleteImage($data['image_url']);
            }
            
            Log::error("Failed to create banner: " . $e->getMessage());
            return back()->withInput()->with('error', 'Lỗi hệ thống! Vui lòng thử lại sau.');
        }
    }

    /**
     * Giao diện chỉnh sửa
     */
    public function edit($id)
    {
        $banner = Banner::findOrFail($id);
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Xử lý cập nhật banner
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        // 1. Validate (truyền ID để validate unique nếu cần, ở đây truyền true là đang update)
        $validated = $this->validateRequest($request, true);

        DB::beginTransaction();
        try {
            $data = [
                'title'     => strip_tags($validated['title']),
                'link'      => $validated['link'],
                'position'  => $validated['position'],
                'priority'  => $request->input('priority', 0),
                'is_active' => $request->has('is_active'),
                'content'   => $request->input('content'),
            ];

            // 2. Xử lý ảnh mới (nếu có)
            if ($request->hasFile('image')) {
                // Xóa ảnh cũ
                $this->deleteImage($banner->image_url);
                // Upload ảnh mới
                $data['image_url'] = $this->uploadImage($request->file('image'));
            }

            // 3. Cập nhật
            $banner->update($data);

            Log::info("Banner updated by Admin ID: " . Auth::id(), ['banner_id' => $banner->id]);

            DB::commit();

            return redirect()->route('admin.banners.index')
                ->with('success', 'Cập nhật banner thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update banner ID $id: " . $e->getMessage());
            return back()->withInput()->with('error', 'Không thể cập nhật. Vui lòng kiểm tra lại.');
        }
    }

    /**
     * Xóa banner (Xóa mềm hoặc xóa cứng tùy requirement, ở đây làm xóa cứng + xóa file)
     */
    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        try {
            // Xóa file ảnh vật lý
            $this->deleteImage($banner->image_url);

            // Xóa bản ghi
            $banner->delete();

            Log::info("Banner deleted by Admin ID: " . Auth::id(), ['banner_id' => $id]);

            return back()->with('success', 'Đã xóa banner và dữ liệu hình ảnh.');

        } catch (\Exception $e) {
            Log::error("Failed to delete banner ID $id: " . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi xóa banner.');
        }
    }

    /**
     * [AJAX] Cập nhật trạng thái nhanh (Toggle Switch)
     * Dùng cho việc bật tắt hiển thị ngay tại trang danh sách
     */
    public function toggleStatus(Request $request, $id)
    {
        if (!$request->ajax()) {
            return response()->json(['message' => 'Method not allowed'], 405);
        }

        try {
            $banner = Banner::findOrFail($id);
            $banner->is_active = !$banner->is_active; // Đảo ngược trạng thái
            $banner->save();

            return response()->json([
                'success' => true, 
                'message' => 'Cập nhật trạng thái thành công!',
                'new_status' => $banner->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi cập nhật'], 500);
        }
    }

    /* -------------------------------------------------------------------------- */
    /* HELPER METHODS                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Validate dữ liệu đầu vào chặt chẽ
     * @param Request $request
     * @param bool $isUpdate
     * @return array
     */
    private function validateRequest(Request $request, $isUpdate = false)
    {
        $rules = [
            'title'     => ['required', 'string', 'max:255'],
            'link'      => ['nullable', 'url', 'max:500'],
            'position'  => ['required', Rule::in(['home_slider', 'home_mid', 'sidebar', 'footer'])],
            'priority'  => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];

        // Validate Ảnh
        // Nếu tạo mới: Bắt buộc (required)
        // Nếu update: Không bắt buộc (nullable)
        $imageRules = [
            $isUpdate ? 'nullable' : 'required',
            'image',
            'mimes:jpeg,png,jpg,webp', // Chỉ cho phép các định dạng ảnh web
            'max:5120',                // Tối đa 5MB
            // 'dimensions:min_width=800,min_height=400' // Có thể bật nếu muốn ép kích thước
        ];

        $rules['image'] = $imageRules;

        $messages = [
            'title.required'    => 'Tiêu đề banner không được để trống.',
            'title.max'         => 'Tiêu đề không được vượt quá 255 ký tự.',
            
            'link.url'          => 'Đường dẫn liên kết phải là một URL hợp lệ (bắt đầu bằng http/https).',
            
            'position.required' => 'Vui lòng chọn vị trí hiển thị.',
            'position.in'       => 'Vị trí hiển thị không hợp lệ.',
            
            'priority.integer'  => 'Thứ tự hiển thị phải là số nguyên.',
            
            'image.required'    => 'Vui lòng tải lên hình ảnh banner.',
            'image.image'       => 'File tải lên phải là hình ảnh.',
            'image.mimes'       => 'Chỉ hỗ trợ định dạng: jpeg, png, jpg, webp.',
            'image.max'         => 'Dung lượng ảnh tối đa là 5MB.',
            'image.dimensions'  => 'Kích thước ảnh không đạt chuẩn (yêu cầu tối thiểu...).',
        ];

        return $request->validate($rules, $messages);
    }

    /**
     * Upload ảnh và trả về đường dẫn
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    private function uploadImage($file)
    {
        // Đặt tên file theo timestamp để tránh trùng lặp: banner_170234234.jpg
        $fileName = 'banner_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Lưu vào storage/app/public/banners
        return $file->storeAs('banners', $fileName, 'public');
    }

    /**
     * Xóa ảnh khỏi Storage
     * @param string|null $path
     */
    private function deleteImage($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}