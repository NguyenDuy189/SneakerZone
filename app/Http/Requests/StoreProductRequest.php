<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Cho phép admin thực hiện
    }

    public function rules()
    {
        return [
            // Thông tin chung
            'name'              => 'required|string|max:255',
            'sku_code'          => 'required|string|unique:products,sku_code,' . $this->route('product'), // Bỏ qua chính nó khi update
            'brand_id'          => 'required|exists:brands,id',
            'category_id'       => 'required|exists:categories,id',
            'price_min'         => 'required|numeric|min:0',
            'status'            => 'required|in:published,draft',
            
            // Ảnh đại diện (Bắt buộc khi tạo mới)
            'image'             => $this->isMethod('post') ? 'required|image|mimes:jpeg,png,jpg,webp|max:2048' : 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            
            // Gallery (Mảng ảnh)
            'gallery.*'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

            // Biến thể (Variants) - Quan trọng cho bán giày
            'variants'          => 'required|array|min:1',
            'variants.*.size'   => 'required', // ID attribute value size
            'variants.*.color'  => 'required', // ID attribute value color
            'variants.*.sku'    => 'required|distinct',
            'variants.*.price'  => 'required|numeric|min:0',
            'variants.*.stock'  => 'required|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'variants.required' => 'Sản phẩm phải có ít nhất một biến thể (Size/Màu).',
            'sku_code.unique'   => 'Mã SKU sản phẩm đã tồn tại.',
        ];
    }
}