<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // 支払い方法は必須。1(コンビニ)か2(カード)のみ許可
            'payment_method' => ['required', 'in:1,2'],
            'post_code' => ['required'],
            'address'     => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'payment_method.required' => '支払い方法を選択してください',
            'post_code.required' => '郵便番号が選択されていません',
            'address.required'     => '住所が選択されていません',
        ];
    }
}