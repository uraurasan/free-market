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
    // 出品画面の表示

    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. パラメータ取得
        $tab = $request->query('tab', 'recommend'); // デフォルトは 'recommend'
        $keyword = $request->query('keyword');      // 検索ワード (ヘッダーと統一！)

        // 2. クエリの準備
        $query = Item::query();

        // ★★★ 検索機能 (No.6) ★★★
        // 商品名で部分一致検索
        if (!empty($keyword)) {
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        // 3. タブ別の処理
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
            // 自分が出品した商品は表示しない
            if ($user) {
                $query->where('user_id', '!=', $user->id);
            }
            $query->orderBy('created_at', 'desc');
        }

        // 4. データ取得
        $items = $query->get();

        // ビューには 'keyword' という名前で渡すで！
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

    // 商品登録処理
    public function store(ExhibitionRequest $request)
    {
        // 1. 画像の保存
        // storage/app/public/item_images に保存されるで
        $imagePath = $request->file('image')->store('item_images', 'public');

        // 2. 商品データの作成
        $item = Item::create([
            'user_id' => Auth::id(), // ログイン中のユーザーID
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image_path' => $imagePath, // 保存したパスをDBへ
            'brand_name' => $request->brand,
            'item_condition' => $request->condition,
            'sales_status' => 1, // 1: 出品中（定数使うのがベストやけど一旦直書き）
        ]);

        // 3. カテゴリーの紐付け (中間テーブルへの保存)
        // これで item_category テーブルにデータが入る！
        $item->categories()->attach($request->categories);


        return redirect()->route('mypage')->with('message', '商品を出品しました！');
    }

    public function show($item_id)
    {
        // 商品情報を取得（カテゴリ、コメント、コメントしたユーザー、いいね も一緒に取ってくる）
        $item = Item::with('categories', 'comments.user', 'favorites')->findOrFail($item_id);

        // カテゴリ（複数あるので配列化、なければ空）
        $categories = $item->categories;

        // 商品の状態定義（定数化すべきやけど一旦ここで！）
        $conditions = [
            1 => '良好',
            2 => '目立った傷や汚れなし',
            3 => 'やや傷や汚れあり',
            4 => '状態が悪い',
        ];
        $conditionName = $conditions[$item->item_condition] ?? '不明';

        // いいね数とコメント数
        $likeCount = $item->favorites->count();
        $commentCount = $item->comments->count();

        return view('item.detail', compact('item', 'categories', 'conditionName', 'likeCount', 'commentCount'));
    }
    // 購入画面表示 (まだ機能は作らんから仮置き！)
    public function purchase($item_id)
    {
        dd('商品ID: ' . $item_id . ' の購入画面やで！後で作るで！');
    }

    public function storeComment(CommentRequest $request, $item_id)
    {
        // バリデーションは CommentRequest で勝手にやってくれるから、
        // ここに到達した時点で「OK」ってことや！
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

        // ログインしてなきゃダメ（フロントでも制御するけど念のため）
        if (!$user_id) {
            return response()->json(['status' => 'error', 'message' => 'Login required'], 401);
        }

        // 既にいいねしてるかチェック（削除済みも含めて探す！）
        $favorite = Favorite::withTrashed()
            ->where('user_id', $user_id)
            ->where('item_id', $item_id)
            ->first();

        if ($favorite) {
            // レコードがある場合
            if ($favorite->trashed()) {
                // 削除されてたら復活（いいね！）
                $favorite->restore();
                $status = 'added';
            } else {
                // 生きてたら削除（いいね解除）
                $favorite->delete();
                $status = 'removed';
            }
        } else {
            // レコードがないなら新規作成（いいね！）
            Favorite::create([
                'user_id' => $user_id,
                'item_id' => $item_id,
            ]);
            $status = 'added';
        }

        // 最新のいいね数を数え直す
        $count = Favorite::where('item_id', $item_id)->count();

        // JSON形式で結果を返す（これがJavaScriptに届く！）
        return response()->json([
            'status' => 'success',
            'like_status' => $status, // added か removed か
            'count' => $count,        // 新しい数字
        ]);
    }
}