<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Hoặc check permission: auth()->user()->can('view_dashboard')
    }

    public function rules()
    {
        return [
            'date_range' => 'nullable|string|in:today,yesterday,7_days,30_days,this_month,last_month,custom',
            'start_date' => 'required_if:date_range,custom|nullable|date|before_or_equal:end_date',
            'end_date'   => 'required_if:date_range,custom|nullable|date|after_or_equal:start_date',
        ];
    }
    
    public function messages() 
    {
        return [
            'start_date.before_or_equal' => 'Ngày bắt đầu phải nhỏ hơn hoặc bằng ngày kết thúc.',
            // Thêm các message tiếng Việt khác...
        ];
    }
}