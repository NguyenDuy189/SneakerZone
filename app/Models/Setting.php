<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['key', 'value', 'type', 'label', 'rules'];

    /**
     * Cast value theo type
     */
    public function getValueAttribute($value)
    {
        switch ($this->type) {
            case 'boolean':
                return (bool)$value;
            case 'integer':
            case 'number':
                return (int)$value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    public function setValueAttribute($value)
    {
        switch ($this->type) {
            case 'boolean':
                $this->attributes['value'] = $value ? 1 : 0;
                break;
            case 'json':
                $this->attributes['value'] = json_encode($value);
                break;
            default:
                $this->attributes['value'] = $value;
        }
    }

    /**
     * Validate giá trị theo rules
     */
    public function validateValue($value)
    {
        $rules = $this->rules ?: 'nullable';

        $validator = Validator::make(['value' => $value], ['value' => $rules], [
            'required' => 'Trường :attribute là bắt buộc.',
            'string'   => 'Trường :attribute phải là chuỗi.',
            'numeric'  => 'Trường :attribute phải là số.',
            'integer'  => 'Trường :attribute phải là số nguyên.',
            'boolean'  => 'Trường :attribute phải là đúng/sai.',
            'json'     => 'Trường :attribute phải là JSON hợp lệ.',
            'email'    => 'Trường :attribute phải là email hợp lệ.',
            'url'      => 'Trường :attribute phải là URL hợp lệ.',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return true;
    }
}
