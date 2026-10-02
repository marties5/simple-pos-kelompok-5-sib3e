<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function create()
    {
        $products = Product::paginate(12);

        return view('pos.create', ['products' => $products]);
    }

    public function store(StoreTransactionRequest $request)
    {
        $validated = $request->validated();

        foreach ($validated['items'] as $index => $item) {
            $product = Product::findOrFail($item['product_id']);

            if ($item['qty'] > $product->stock) {
                return redirect()
                    ->route('pos.create')
                    ->withErrors([
                        "items.$index.qty" =>
                            "Stok produk {$product->name} tidak mencukupi. Stok tersedia: {$product->stock}."
                    ]);
            }
        }

        DB::transaction(function () use ($validated) {
            $transaction = Transaction::create([
                'user_id' => 1,
                'total' => 0,
            ]);

            $total = 0;

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                $subtotal = $product->price * $item['qty'];
                $total += $subtotal;

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'subtotal' => $subtotal,
                ]);
            }

            $transaction->update([
                'total' => $total,
            ]);
        });

        return redirect()
            ->route('pos.create')
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    public function index()
    {
        $transactions = Transaction::with('details.product')
            ->latest()
            ->paginate(15);

        return view('transactions.index', compact('transactions'));
    }

    public function show(string $id)
    {
        return "Detail transaksi #{$id}";
    }
}