<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExhibitionRequest;
use App\Http\Requests\CommentRequest;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Item;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        $tab = $request->query('tab', 'recommend');
        $keyword = $request->query('keyword');

        $query = Item::query();

        if (!empty($keyword)) {
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        if ($tab === 'mylist') {
            if (!$user) {
                $items = collect();
                return view('item.index', compact('items', 'tab', 'keyword'));
            }

            $query->whereHas('favorites', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

            $query->orderBy('created_at', 'desc');

        } else {
            if ($user) {
                $query->where('user_id', '!=', $user->id);
            }
            $query->orderBy('created_at', 'desc');
        }

        $items = $query->get();

        return view('item.index', compact('items', 'tab', 'keyword'));
    }

    public function create()
    {
        $categories = Category::all();

        $conditions = [
            1 => '良好',
            2 => '目立った傷や汚れなし',
            3 => 'やや傷や汚れあり',
            4 => '状態が悪い',
        ];

        return view('item.create', compact('categories', 'conditions'));
    }

    public function store(ExhibitionRequest $request)
    {
        $imagePath = $request->file('image')->store('item_images', 'public');

        $item = Item::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image_path' => $imagePath,
            'brand_name' => $request->brand,
            'item_condition' => $request->condition,
            'sales_status' => 1,
        ]);

        $item->categories()->attach($request->categories);

        return redirect()->route('mypage')->with('message', '商品を出品しました！');
    }

    public function show($item_id)
    {
        $item = Item::with('categories', 'comments.user', 'favorites')->findOrFail($item_id);

        $categories = $item->categories;

        $conditions = [
            1 => '良好',
            2 => '目立った傷や汚れなし',
            3 => 'やや傷や汚れあり',
            4 => '状態が悪い',
        ];
        $conditionName = $conditions[$item->item_condition] ?? '不明';

        $likeCount = $item->favorites->count();
        $commentCount = $item->comments->count();

        return view('item.detail', compact('item', 'categories', 'conditionName', 'likeCount', 'commentCount'));
    }

    public function purchase($item_id)
    {
        dd('商品ID: ' . $item_id . ' の購入画面やで！後で作るで！');
    }

    public function storeComment(CommentRequest $request, $item_id)
    {
        $item = Item::findOrFail($item_id);

        $item->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        return redirect()->route('item.detail', ['item_id' => $item->id])
            ->with('message', 'コメントを送信しました！');
    }

    public function like($item_id)
    {
        $user_id = Auth::id();

        if (!$user_id) {
            return response()->json(['status' => 'error', 'message' => 'Login required'], 401);
        }

        $favorite = Favorite::withTrashed()
            ->where('user_id', $user_id)
            ->where('item_id', $item_id)
            ->first();

        if ($favorite) {
            if ($favorite->trashed()) {
                $favorite->restore();
                $status = 'added';
            } else {
                $favorite->delete();
                $status = 'removed';
            }
        } else {
            Favorite::create([
                'user_id' => $user_id,
                'item_id' => $item_id,
            ]);
            $status = 'added';
        }

        $count = Favorite::where('item_id', $item_id)->count();

        return response()->json([
            'status' => 'success',
            'like_status' => $status,
            'count' => $count,
        ]);
    }
}