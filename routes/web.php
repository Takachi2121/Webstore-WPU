<?php

use App\Http\Controllers\ProductController;
use App\Livewire\Checkout;
use App\Livewire\SalesOrderDetail;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'home-page')->name('home');
Route::livewire('/products', 'product-catalog')->name('product-catalog');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product');
Route::livewire('/cart', 'cart')->name('cart');
Route::get('/checkout', Checkout::class)->name('checkout');
Route::get('/order-confirmed/{sales_order:trx_id}', SalesOrderDetail::class)->name('order-confirmed');
Route::view('/page', 'pages.page')->name('page');
