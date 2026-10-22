<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Admin melihat semua pesanan, user hanya melihat miliknya.
    public function index(Request $request)
    {
        $query = Order::with(['user:id,name', 'menu:id,nama,harga'])->latest();

        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json($query->get());
    }

    // Admin dapat membuat pesanan untuk user tertentu.
    public function store(Request $request)
    {
        $data = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'jumlah' => 'required|integer|min:1|max:100',
            'catatan' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $isAdmin = $request->user()->role === 'admin';
        $data['user_id'] = ($isAdmin && ! empty($data['user_id']))
            ? $data['user_id']
            : $request->user()->id;

        $order = Order::create($data);

        return response()->json($order->load(['user:id,name', 'menu:id,nama,harga']), 201);
    }

    // User hanya dapat mengubah pesanan sendiri yang masih pending.
    public function update(Request $request, Order $order)
    {
        $isAdmin = $request->user()->role === 'admin';

        if (! $isAdmin) {
            if ($order->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Bukan pesananmu'], 403);
            }

            if ($order->status !== 'pending') {
                return response()->json(['message' => 'Pesanan sudah diproses, tidak bisa diubah'], 422);
            }
        }

        $rules = [
            'jumlah' => 'required|integer|min:1|max:100',
            'catatan' => 'nullable|string|max:255',
        ];

        if ($isAdmin) {
            $rules['status'] = 'sometimes|in:pending,diproses,selesai';
        }

        $order->update($request->validate($rules));

        return response()->json($order->load(['user:id,name', 'menu:id,nama,harga']));
    }

    // Route ini dilindungi middleware admin.
    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json(['message' => 'Pesanan dihapus']);
    }
}
