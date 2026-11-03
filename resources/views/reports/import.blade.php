@extends('layouts.app')

@section('title', 'Import Produk')

@section('content')
<div class="max-w-xl mb-8">
    <h1 class="text-lg font-semibold mb-4 text-slate-800">Import Produk dari CSV</h1>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 p-3 rounded-md mb-4 text-sm border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">File CSV</label>
            <input type="file" name="csv" accept=".csv" 
                class="block w-full text-sm text-slate-500
                       file:mr-4 file:py-2 file:px-4
                       file:rounded-md file:border-0
                       file:text-sm file:font-semibold
                       file:bg-slate-900 file:text-white
                       hover:file:bg-slate-800
                       cursor-pointer border border-slate-300 rounded-md p-1 bg-white">
            @error('csv')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <p class="text-xs text-slate-500">
            Kolom yang dibutuhkan pada file CSV: <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-700">name</code>, <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-700">category_id</code>, <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-700">price</code>.
        </p>

        <div>
            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                Import
            </button>
        </div>
    </form>
</div>

@if(isset($importErrors) && $importErrors->count() > 0)
<div class="max-w-4xl mt-6">
    <h2 class="text-md font-semibold mb-3 text-red-600">Log Baris Gagal Di-import ({{ $importErrors->count() }})</h2>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-slate-200 text-sm bg-white">
            <thead>
                <tr class="bg-slate-100 text-slate-700">
                    <th class="border border-slate-200 p-2 text-center">Baris ke-</th>
                    <th class="border border-slate-200 p-2 text-left">Isi Data Row</th>
                    <th class="border border-slate-200 p-2 text-left">Pesan Error</th>
                </tr>
            </thead>
            <tbody>
                @foreach($importErrors as $err)
                <tr class="hover:bg-slate-50">
                    <td class="border border-slate-200 p-2 text-center text-slate-600">{{ $err->row_number }}</td>
                    <td class="border border-slate-200 p-2 font-mono text-xs text-slate-700">{{ $err->row_data }}</td>
                    <td class="border border-slate-200 p-2 text-red-600">{{ $err->error_message }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection