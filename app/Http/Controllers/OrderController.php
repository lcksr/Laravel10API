<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Food;  // Model untuk produk makanan
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;  // Import DB facade untuk transaksi

class OrderController extends Controller
{
    public function createOrder(Request $request)
{
    $user = auth()->user();  
    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);  // Jika tidak terautentikasi
    }

    // Validasi input
    $validator = Validator::make($request->all(), [
        'total_price' => 'required|numeric|min:0',
        'delivery_address' => 'required|string|max:255',
        'order_items' => 'required|array|min:1',
        'order_items.*.food_id' => 'required|exists:foods,id',
        'order_items.*.quantity' => 'required|integer|min:1',
        'order_items.*.price' => 'required|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    DB::beginTransaction();

    try {
        // Membuat order baru
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => $request->input('total_price'),
            'status' => 'pending',
            'delivery_address' => $request->input('delivery_address')
        ]);

        // Menambahkan item ke dalam order
        foreach ($request->input('order_items') as $item) {
            $food = Food::find($item['food_id']);  // Cari data makanan berdasarkan food_id

            // Cek stok makanan
            if ($food->stock < $item['quantity']) {
                DB::rollback();
                return response()->json(['message' => "Not enough stock for {$food->name}"], 400);
            }

            // Mengurangi stok makanan
            $food->stock -= $item['quantity'];
            $food->save();

            // Menambahkan item pesanan
            OrderItem::create([
                'order_id' => $order->id,
                'food_id' => $item['food_id'],
                'food_name' => $food->name, // Simpan nama makanan ke kolom food_name
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ]);
        }

        DB::commit();

        return response()->json(['order' => $order], 201);
    } catch (\Exception $e) {
        DB::rollback();
        return response()->json(['message' => 'Server Error', 'error' => $e->getMessage()], 500);
    }
}


public function getUserOrders(Request $request)
{
    $user = auth()->user();

    // Ambil semua pesanan pengguna beserta itemnya
    $orders = Order::where('user_id', $user->id)
        ->with(['orderItems' => function ($query) {
            $query->select('order_id', 'food_name', 'quantity', 'price'); // Ambil kolom yang diperlukan
        }])
        ->get();

    // Jika tidak ada pesanan
    if ($orders->isEmpty()) {
        return response()->json(['message' => 'Tidak ada riwayat pesanan, silakan pesan terlebih dahulu.'], 200);
    }

    // Format data untuk dikembalikan
    $response = $orders->map(function ($order) {
        return [
            'order_id' => $order->id,
            'delivery_address' => $order->delivery_address,
            'total_price' => $order->total_price,
            'status' => $order->status,
            'items' => $order->orderItems->map(function ($item) {
                return [
                    'food_name' => $item->food_name, // Ambil nama makanan
                    'quantity' => $item->quantity,  // Kuantitas
                    'price' => $item->price,        // Total harga untuk item ini
                ];
            }),
        ];
    });

    return response()->json(['orders' => $response], 200);
}
}