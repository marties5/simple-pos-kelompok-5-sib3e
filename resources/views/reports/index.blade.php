@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold mb-4 text-slate-800">Laporan Penjualan</h1>

    <!-- Form Filter & Tombol Export Berjejer di Samping Tombol Filter -->
    <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap gap-3 items-end mb-6">
        <label class="block">
            <span class="text-sm font-medium text-slate-700">Dari</span>
            <input type="date" name="from" value="{{ $from }}" class="mt-1 block border border-slate-300 rounded-md px-3 py-2 text-sm bg-white">
        </label>
        
        <label class="block">
            <span class="text-sm font-medium text-slate-700">Sampai</span>
            <input type="date" name="to" value="{{ $to }}" class="mt-1 block border border-slate-300 rounded-md px-3 py-2 text-sm bg-white">
        </label>

        <!-- Dropdown Kategori -->
        <label class="block">
            <span class="text-sm font-medium text-slate-700">Kategori</span>
            <select name="category_id" class="mt-1 block border border-slate-300 rounded-md px-3 py-2 text-sm bg-white">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ isset($selectedCategory) && $selectedCategory == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <!-- Tombol Filter -->
        <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-md text-sm font-medium transition">
            Filter
        </button>

        <!-- Tombol Export Berada di Samping Tombol Filter -->
        <div class="flex gap-2">
            <a href="{{ route('reports.export.csv', request()->all()) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium transition">Export CSV</a>
            <a href="{{ route('reports.export.xlsx', request()->all()) }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md text-sm font-medium transition">Export Excel</a>
            <a href="{{ route('reports.export.pdf', request()->all()) }}" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium transition">Export PDF</a>
        </div>
    </form>

    <!-- Ringkasan Laporan -->
    <div class="bg-white border border-slate-200 rounded-lg p-4 max-w-sm shadow-sm">
        <p class="text-sm text-slate-500">Total Penjualan</p>
        <p class="text-2xl font-semibold text-slate-800">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</p>
        <p class="text-sm text-slate-500 mt-2">{{ $jumlahTransaksi }} transaksi</p>
    </div>
</div>
@endsection