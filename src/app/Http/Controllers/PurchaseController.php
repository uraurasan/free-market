<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\ShippingDestination;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\AddressRequest; // ← 追加
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PurchaseController extends Controller
{
    // 1. 購入画面表示
    public function index($item_id)
    {
        $user = Auth::user();
        $item = Item::findOrFail($item_id);

        if ($item->user_id === $user->id) {
             return abort(403, '自分の商品は購入できません');
        }
        if ($item->sales_status !== 1) {
             return abort(403, 'この商品は売り切れです');
        }

        // セッションにある住所を優先して表示（なければユーザー情報）
        $addressData = session('temp_address_' . $item_id) ?? [
            'post_code' => $user->post_code,
            'address' => $user->address,
            'building_name' => $user->building_name,
        ];

        return view('purchase.index', compact('item', 'user', 'addressData'));
    }

    // 2. 決済開始処理 (checkout) ★これが消えてたはず！
    public function checkout(PurchaseRequest $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        if ($item->user_id === $user->id || $item->sales_status !== 1) {
            return abort(404);
        }

        // フォームから送られてきた住所情報（またはセッションの情報）をまとめる
        // 住所変更画面を経由している場合は $request に入っている値を使うのが確実
        // （hiddenフィールドで送っているため）
        session([
            'purchase_data' => [
                'item_id' => $item_id,
                'payment_method' => $request->payment_method,
                'post_code' => $request->post_code,
                'address' => $request->address,
                'building_name' => $request->building_name,
            ]
        ]);

        // Stripe初期化
        Stripe::setApiKey(config('services.stripe.secret'));

        // Stripeセッション作成
        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $item->name,
                    ],
                    'unit_amount' => $item->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchase.success', ['item_id' => $item_id]),
            'cancel_url' => route('purchase.cancel', ['item_id' => $item_id]),
        ]);

        return redirect($checkout_session->url);
    }

    // 3. 決済成功時の処理 (success)
    public function success($item_id)
    {
        $data = session('purchase_data');

        if (!$data || $data['item_id'] != $item_id) {
            return redirect()->route('root')->with('error', 'セッションがタイムアウトしました。もう一度やり直してください。');
        }

        $user = Auth::user();
        $item = Item::findOrFail($item_id);

        // 1:コンビニ(支払い待ち=2), 2:カード(支払い完了=1)
        $payment_status = ($data['payment_method'] == 1) ? 2 : 1;

        DB::transaction(function () use ($user, $item, $data, $payment_status) {
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
                'payment_method' => $data['payment_method'],
                'payment_status' => $payment_status, 
            ]);

            ShippingDestination::create([
                'purchase_id' => $purchase->id,
                'post_code' => $data['post_code'],
                'address' => $data['address'],
                'building_name' => $data['building_name'],
            ]);

            $item->update(['sales_status' => 2]); 
        });

        session()->forget('purchase_data');
        // 住所変更用の一時セッションも消しておく
        session()->forget('temp_address_' . $item_id);

        $msg = ($payment_status == 2) ? '購入手続きが完了しました。コンビニでお支払いください。' : '購入が完了しました！';

        return redirect()->route('root')->with('message', $msg);
    }

    // 4. 決済キャンセル時の処理 (cancel)
    public function cancel($item_id)
    {
        return redirect()->route('item.purchase', ['item_id' => $item_id])
            ->with('error', '購入手続きをキャンセルしました。');
    }

    // 5. 住所変更画面表示 (editAddress)
    public function editAddress($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();
        
        $addressData = session('temp_address_' . $item_id) ?? [
            'post_code' => $user->post_code,
            'address' => $user->address,
            'building_name' => $user->building_name,
        ];

        return view('purchase.address', compact('item', 'addressData'));
    }

    // 6. 住所変更処理 (updateAddress)
    public function updateAddress(AddressRequest $request, $item_id)
    {
        session([
            'temp_address_' . $item_id => [
                'post_code' => $request->post_code,
                'address' => $request->address,
                'building_name' => $request->building_name,
            ]
        ]);

        return redirect()->route('item.purchase', ['item_id' => $item_id]);
    }
}