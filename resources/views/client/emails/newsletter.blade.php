<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chào mừng đến với Sneaker Zone</title>
    <style>
        /* CSS Reset cơ bản cho Email */
        body { margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        table { border-collapse: collapse; width: 100%; }
        img { border: 0; display: block; line-height: 100%; outline: none; text-decoration: none; }
        
        /* Responsive cho mobile */
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .content-padding { padding: 20px !important; }
            .header-text { font-size: 24px !important; }
        }
    </style>
</head>
<body style="background-color: #f3f4f6;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding: 40px 0;">
                
                <table border="0" cellpadding="0" cellspacing="0" width="600" class="container" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                    
                    <tr>
                        <td align="center" style="background-color: #111827; padding: 30px;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; letter-spacing: 2px; text-transform: uppercase;">SNEAKER ZONE</h1>
                        </td>
                    </tr>

                    <tr>
                        <td align="center">
                            <img src="https://images.unsplash.com/photo-1552346154-21d32810aba3?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=300&q=80" 
                                 alt="Welcome Banner" width="600" style="width: 100%; height: auto;">
                        </td>
                    </tr>

                    <tr>
                        <td class="content-padding" style="padding: 40px 40px 20px 40px;">
                            <h2 class="header-text" style="color: #111827; font-size: 28px; font-weight: 700; margin: 0 0 20px 0;">Đăng ký thành công! 🎉</h2>
                            
                            <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
                                Xin chào, <br><br>
                                Cảm ơn bạn đã quan tâm và đăng ký nhận tin từ <strong>Sneaker Zone</strong>.
                                Bạn đã chính thức gia nhập cộng đồng những người đam mê giày sneaker hàng đầu.
                            </p>
                            
                            <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
                                Chúng tôi sẽ gửi đến bạn những bộ sưu tập mới nhất, mã giảm giá độc quyền và những xu hướng thời trang đang hot nhất thị trường.
                            </p>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <a href="{{ url('/') }}" style="background-color: #4f46e5; color: #ffffff; padding: 14px 32px; display: inline-block; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 16px; box-shadow: 0 4px 6px rgba(79, 70, 229, 0.4);">
                                            Khám phá Website ngay
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 40px 40px 40px; text-align: center;">
                            <p style="color: #9ca3af; font-size: 14px; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
                                Nếu bạn có bất kỳ câu hỏi nào, đừng ngần ngại trả lời email này. Chúng tôi luôn sẵn sàng hỗ trợ bạn!
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px; text-align: center;">
                            <p style="color: #6b7280; font-size: 12px; margin: 0;">
                                &copy; {{ date('Y') }} Sneaker Zone. All rights reserved.
                            </p>
                            <div style="margin-top: 10px;">
                                <a href="#" style="color: #4f46e5; text-decoration: none; font-size: 12px; margin: 0 5px;">Facebook</a>
                                <span style="color: #d1d5db;">|</span>
                                <a href="#" style="color: #4f46e5; text-decoration: none; font-size: 12px; margin: 0 5px;">Instagram</a>
                                <span style="color: #d1d5db;">|</span>
                                <a href="#" style="color: #4f46e5; text-decoration: none; font-size: 12px; margin: 0 5px;">Website</a>
                            </div>
                        </td>
                    </tr>

                </table>
                <p style="text-align: center; color: #9ca3af; font-size: 12px; margin-top: 20px;">
                    Bạn nhận được email này vì đã đăng ký tại Sneaker Zone.
                </p>

            </td>
        </tr>
    </table>

</body>
</html>