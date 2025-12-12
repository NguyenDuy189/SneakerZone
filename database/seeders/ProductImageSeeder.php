<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy tất cả sản phẩm đang có
        $products = Product::all();

        foreach ($products as $product) {
            // Mỗi sản phẩm tạo thêm 3 ảnh phụ (Gallery)
            // Dùng ảnh giày đẹp từ Unsplash để demo cho chuyên nghiệp
            
            $images = [
                'https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?q=80&w=1000&auto=format&fit=crop', // Góc nghiêng
                'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?q=80&w=1000&auto=format&fit=crop', // Chi tiết đế
                'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=1000&auto=format&fit=crop', // Trên chân
            ];

            foreach ($images as $index => $url) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $url, // Lưu ý: Ở môi trường thật, đây sẽ là đường dẫn 'products/abc.jpg'
                    'sort_order' => $index
                ]);
            }
        }
    }
}