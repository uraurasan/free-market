<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    /**
     * 権限チェック（今回は全員OK）
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            // 商品名: 必須, 文字列, (念のため255文字制限入れとくのがDB的に安全や)
            'name' => ['required', 'string', 'max:255'],

            // 商品説明: 必須, 文字列, 最大255文字
            'description' => ['required', 'string', 'max:255'],

            // 商品画像: 必須, 画像ファイル, jpeg/pngのみ, サイズ制限(例:10MB)も入れとくと安心
            'image' => ['required', 'image', 'mimes:jpeg,png'],

            // カテゴリー: 必須, 配列であること（複数選択だから）
            'categories' => ['required', 'array'],
            // 配列の中身がちゃんと存在するかチェック（任意やけど堅牢にするなら入れる）
            'categories.*' => ['exists:categories,id'],

            // 商品の状態: 必須, 選択肢のキー(1~4)であること
            'condition' => ['required', 'integer', 'min:1', 'max:4'],

            // 商品価格: 必須, 数値, 0円以上
            'price' => ['required', 'integer', 'min:0', 'max:99999999'],
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages()
    {
        return [
            'name.required' => '商品名を入力してください',
            'description.required' => '商品説明を入力してください',
            'description.max' => '商品説明は255文字以内で入力してください',
            'image.required' => '商品画像を選択してください',
            'image.mimes' => '商品画像はjpegまたはpng形式でアップロードしてください',
            'categories.required' => 'カテゴリーを選択してください',
            'condition.required' => '商品の状態を選択してください',
            'price.required' => '販売価格を入力してください',
            'price.integer' => '販売価格は数値で入力してください',
            'price.min' => '販売価格は0円以上で入力してください',
            'price.max' => '販売価格は9,999,999円以下で入力してください',
        ];
    }
}