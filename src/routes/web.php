<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ItemController::class, 'index'])->name('root');
Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('item.detail');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/item/{item_id}/like', [ItemController::class, 'like'])->name('item.like');
    Route::post('/item/{item_id}/comment', [ItemController::class, 'storeComment'])->name('item.comment.store');
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'index'])->name('item.purchase');
    Route::post('/purchase/checkout/{item_id}', [PurchaseController::class, 'checkout'])->name('purchase.checkout');
    Route::get('/purchase/success/{item_id}', [PurchaseController::class, 'success'])->name('purchase.success');
    Route::get('/purchase/cancel/{item_id}', [PurchaseController::class, 'cancel'])->name('purchase.cancel');
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.address');
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');
    Route::get('/mypage', [ProfileController::class, 'index'])->name('mypage');
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/mypage/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/sell', [ItemController::class, 'create'])->name('sell');
    Route::post('/sell', [ItemController::class, 'store'])->name('item.store');
});