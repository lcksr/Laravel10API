<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderHistoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rute untuk registrasi
Route::post('register', [AuthController::class, 'register']);

// Rute untuk login
Route::post('login', [AuthController::class, 'login']);

// Rute untuk logout (memerlukan autentikasi)
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

// Rute untuk mendapatkan data profil pengguna (memerlukan autentikasi)
Route::middleware('auth:api')->get('user-profile', [AuthController::class, 'userProfile']);

// Rute untuk edit profil (memerlukan autentikasi)
Route::middleware('auth:api')->put('edit-profile', [AuthController::class, 'editProfile']);

// Pencarian menu (akses publik)
Route::get('search', [FoodController::class, 'search']);

// Menambah makanan baru (hanya untuk admin yang terautentikasi)
Route::middleware('auth:api')->post('foods', [FoodController::class, 'store']);

// Merubah menu
Route::middleware('auth:api')->put('/foods/{name}', [FoodController::class, 'update']);


// Menampilkan daftar makanan (akses publik)
Route::get('foods', [FoodController::class, 'index']);

// Membuat pesanan (hanya untuk pengguna yang terautentikasi)
Route::middleware('auth:api')->post('order', [OrderController::class, 'createOrder']);

// Mengambil histori pesanan pengguna (hanya untuk pengguna yang terautentikasi)
Route::middleware('auth:api')->get('orders', [OrderController::class, 'getUserOrders']);

// Jika ingin rute terpisah untuk histori pesanan
Route::middleware('auth:api')->get('history', [OrderController::class, 'getOrderHistory']);

Route::get('/menus', [FoodController::class, 'index1']);

Route::get('/menus/category/{categoryId}', [FoodController::class, 'getByCategory']);
