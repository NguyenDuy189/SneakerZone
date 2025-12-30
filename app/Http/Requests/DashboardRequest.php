<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date_range' => 'nullable|string|in:custom,today,yesterday,7_days,30_days,this_month,last_month',
            'start_date' => 'nullable|required_if:date_range,custom|date|before_or_equal:end_date',
            'end_date'   => 'nullable|required_if:date_range,custom|date|after_or_equal:start_date',
        ];
    }
}