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

    public function update(ProfileRequest $request)
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
            $items = Purchase::where('user_id', $user->id)
                ->with('item')
                ->orderBy('created_at', 'desc')
                ->get()
                ->pluck('item');
        } else {
            $items = Item::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('mypage.index', compact('user', 'items', 'tab'));
    }
}