<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\ShippingDestination;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\AddressRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PurchaseController extends Controller
{
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

        $addressData = session('temp_address_' . $item_id) ?? [
            'post_code' => $user->post_code,
            'address' => $user->address,
            'building_name' => $user->building_name,
        ];

        return view('purchase.index', compact('item', 'user', 'addressData'));
    }

    public function checkout(PurchaseRequest $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        if ($item->user_id === $user->id || $item->sales_status !== 1) {
            return abort(404);
        }

        session([
            'purchase_data' => [
                'item_id' => $item_id,
                'payment_method' => $request->payment_method,
                'post_code' => $request->post_code,
                'address' => $request->address,
                'building_name' => $request->building_name,
            ]
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

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

    public function success($item_id)
    {
        $data = session('purchase_data');

        if (!$data || $data['item_id'] != $item_id) {
            return redirect()->route('root')->with('error', 'セッションがタイムアウトしました。もう一度やり直してください。');
        }

        $user = Auth::user();
        $item = Item::findOrFail($item_id);

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
        session()->forget('temp_address_' . $item_id);

        $msg = ($payment_status == 2) ? '購入手続きが完了しました。コンビニでお支払いください。' : '購入が完了しました！';

        return redirect()->route('root')->with('message', $msg);
    }

    public function cancel($item_id)
    {
        return redirect()->route('item.purchase', ['item_id' => $item_id])
            ->with('error', '購入手続きをキャンセルしました。');
    }

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