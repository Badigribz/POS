<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
        public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string',
            'mpesa_code' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request) {
            $totalAmount = 0;

            // Create the sale
            $sale = Sale::create([
                'cashier_id' => auth()->id(),
                'total_amount' => 0,
                'payment_method' => $request->payment_method,
                'mpesa_code' => $request->mpesa_code,
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                \Log::info('Debug Stock Check', [
                    'product_name' => $product->name,
                    'current_stock' => $product->quantity,
                    'sale_quantity' => $item['quantity']
                ]);

                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Not enough stock for {$product->name}");
                }

                // Deduct stock
                $product->quantity -= $item['quantity'];
                $product->save();

                // Add sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);

                $totalAmount += $product->price * $item['quantity'];
            }

            // Update sale total
            $sale->update(['total_amount' => $totalAmount]);
        });

        return response()->json(['message' => 'Sale recorded successfully']);
    }

        public function report(Request $request)
    {
        $query = Sale::with(['cashier', 'items.product'])
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->end_date);
            })
            ->when($request->cashier_id, function ($q) use ($request) {
                $q->where('cashier_id', $request->cashier_id);
            });

        $sales = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'total_revenue' => $sales->sum('total_amount'),
            'total_sales' => $sales->count(),
            'sales' => $sales
        ]);
   }

}
