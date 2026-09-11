@extends('layouts.app') 
 
@section('title', 'Kasir') 
 
@section('content') 
    <h1 class="text-lg font-semibold mb-4">Transaksi Kasir</h1> 
 
    <div x-data="{ 
        cart: [], 
        addToCart(id, name, price) { 
            this.cart.push({ id, name, price }); 
        }, 
        removeFromCart(id) { 
            this.cart = this.cart.filter(item => item.id !== id); 
        }, 
        subtotal() { 
            return this.cart.reduce((sum, item) => sum + item.price, 0); 
        } 
    }"> 
 
        <div class="grid grid-cols-3 gap-4"> 
            @foreach ($products as $product) 
                <div class="border rounded-md p-3 cursor-pointer" 
                    @click="addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }})"> 
                    
                    <p class="font-medium">{{ $product->name }}</p> 
                    
                    <p class="text-sm text-slate-500">
                        Rp {{ number_format($product->price) }}
                    </p>

                    @if ($product->stock < 10)
                        <span class="inline-block text-xs px-2 py-1 mt-1 rounded bg-amber-100 text-amber-700">
                            Stok Menipis
                        </span>
                    @endif

                </div> 
            @endforeach 
        </div> 
 
        <div class="mt-4 border-t pt-3"> 
            <template x-for="item in cart" :key="item.id"> 
                <div class="flex items-center gap-3 mb-2">
                    <p x-text="item.name + ' - Rp ' + item.price"></p>
                    
                    <button 
                        type="button"
                        @click="removeFromCart(item.id)"
                        class="px-3 py-1.5 text-sm font-medium text-white bg-red-500 rounded-md hover:bg-red-600">
                        Hapus
                    </button>
                </div>
            </template> 
 
            <p class="font-semibold mt-2"> 
                Subtotal: Rp <span x-text="subtotal()"></span> 
            </p> 
        </div> 
 
    </div> 
@endsection