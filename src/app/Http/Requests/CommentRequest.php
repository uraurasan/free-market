<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    /**
     * 権限チェック
     * ログインしてるユーザーなら誰でもコメントOKにするので true
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
            // 必須、文字列、最大255文字
            'comment' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages()
    {
        return [
            'comment.required' => 'コメントを入力してください',
            'comment.string' => 'コメントは文字列で入力してください',
            'comment.max' => 'コメントは255文字以内で入力してください',
        ];
    }
}