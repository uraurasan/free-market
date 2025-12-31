<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileRequest;
use App\Models\Item;
use App\Models\Purchase;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('mypage.profile', compact('user'));
    }

    public function update(ProfileRequest $request) // ← ここを Request から ProfileRequest に変更！
    {
        $user = Auth::user();
        $data = $request->validated();

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $data['profile_image'] = $path;
        }

        $user->fill($data)->save();

        return redirect()->route('mypage')->with('message', 'プロフィールを更新しました。');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'sell');

        if ($tab === 'buy') {
            // 【修正版】最強の書き方！
            // 1. 自分が買った履歴(Purchase)を取ってくる
            // 2. その履歴に紐づく商品(item)も一緒に持ってくる ('with')
            // 3. 最後に pluck('item') で「商品データのリスト」だけに変換する
            $items = Purchase::where('user_id', $user->id)
                ->with('item') // Purchaseモデルに public function item() がある前提
                ->orderBy('created_at', 'desc') // 新しい順にする
                ->get()
                ->pluck('item'); // ここで Item のコレクションに変換！
        } else {
            // 出品した商品
            $items = Item::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('mypage.index', compact('user', 'items', 'tab'));
    }
}