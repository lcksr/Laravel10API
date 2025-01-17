<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\orderHistory;

class OrderHistoryController extends Controller
{
    public function getOrderHistory($orderId)
    {
        $history = OrderHistory::where('order_id', $orderId)->get();

        if ($history->isEmpty()) {
            return response()->json([
                'message' => 'Belum ada transaksi untuk pesanan ini.',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'message' => 'Berhasil mengambil histori pesanan.',
            'data' => $history,
        ], 200);
    }
}

