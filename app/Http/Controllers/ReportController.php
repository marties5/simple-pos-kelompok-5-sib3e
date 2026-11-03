<?php

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Models\ImportError;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->resolveDateRange($request);
        $categoryId = $request->input('category_id'); // Menangkap filter kategori
        $query = $this->filteredQuery($from, $to, $categoryId);

        return view('reports.index', [
            'totalPenjualan'   => $query->sum('total'),
            'jumlahTransaksi'  => $query->count(),
            'from'             => $from->toDateString(),
            'to'               => $to->toDateString(),
            'categories'       => \App\Models\Category::all(), // Data untuk dropdown kategori
            'selectedCategory' => $categoryId,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveDateRange($request);
        $query = $this->filteredQuery($from, $to);

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Tanggal', 'Kasir', 'Total']);

            foreach ($query->lazyById() as $t) {
                fputcsv($out, [$t->id, $t->created_at, $t->user->name, $t->total]);
            }

            fclose($out);
        }, 'laporan-penjualan.csv');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        [$from, $to] = $this->resolveDateRange($request);

        return Excel::download(
            new SalesReportExport($this->filteredQuery($from, $to)),
            'laporan-penjualan.xlsx'
        );
    }

    public function exportPdf(Request $request): Response
    {
        [$from, $to] = $this->resolveDateRange($request);

        $pdf = Pdf::loadView('reports.pdf', [
            'totalPenjualan'  => $this->filteredQuery($from, $to)->sum('total'),
            'jumlahTransaksi' => $this->filteredQuery($from, $to)->count(),
            'transactions'    => $this->filteredQuery($from, $to)->latest()->limit(50)->get(),
            'bestSellers'     => $this->bestSellingProducts($from, $to),
            'from'            => $from->toDateString(),
            'to'              => $to->toDateString(),
        ]);

        return $pdf->download('laporan-penjualan.pdf');
    }

    public function importForm(): View
    {
        return view('reports.import', [
            'importErrors' => ImportError::latest()->get()
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $request->file('csv');
        $filename = $file->getClientOriginalName();
        
        // Bersihkan log error sebelumnya
        ImportError::truncate();

        $lines = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $rows = array_map('str_getcsv', $lines);
        $header = array_shift($rows);
        
        $created = 0;
        $updated = 0;
        $rowNumber = 1;

        foreach ($rows as $row) {
            $rowNumber++;
            
            if (count($row) !== count($header)) {
                ImportError::create([
                    'filename' => $filename,
                    'row_number' => $rowNumber,
                    'row_data' => implode(', ', $row),
                    'error_message' => 'Jumlah kolom tidak sesuai dengan header.',
                ]);
                continue;
            }

            $validator = Validator::make(array_combine($header, $row), [
                'name' => ['required', 'string'],
                'category_id' => ['required', 'exists:categories,id'],
                'price' => ['required', 'integer', 'min:0'],
            ]);

            if ($validator->fails()) {
                // Terjemahkan pesan error bawaan Laravel ke Bahasa Indonesia
                $customErrors = collect($validator->errors()->all())->map(function ($message) {
                    if (str_contains($message, 'category id is invalid')) {
                        return 'Kategori yang dipilih tidak valid / tidak ditemukan.';
                    }
                    if (str_contains($message, 'price field must be an integer')) {
                        return 'Harga harus berupa angka (integer).';
                    }
                    return $message;
                });

                ImportError::create([
                    'filename' => $filename,
                    'row_number' => $rowNumber,
                    'row_data' => implode(', ', $row),
                    'error_message' => $customErrors->implode(' | '),
                ]);
                continue;
            }

            $data = $validator->validated();
            $product = Product::updateOrCreate(
                ['name' => $data['name'], 'category_id' => $data['category_id']],
                ['price' => $data['price']],
            );

            $product->wasRecentlyCreated ? $created++ : $updated++;
        }

        return back()->with('success', "{$created} produk baru, {$updated} diperbarui. Cek tabel di bawah jika ada baris yang gagal.");
    }

    private function resolveDateRange(Request $request): array
    {
        $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
        $to   = $request->date('to')?->endOfDay() ?? now()->endOfDay();

        return [$from, $to];
    }

    private function filteredQuery($from, $to, $categoryId = null): Builder
    {
        $query = Transaction::with(['user', 'details.product'])
            ->whereBetween('created_at', [$from, $to]);

        // Jika filter kategori dipilih, saring transaksi berdasarkan produk pada kategori tersebut
        if (!empty($categoryId)) {
            $query->whereHas('details.product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        return $query;
    }

    private function bestSellingProducts($from, $to)
    {
        return TransactionDetail::query()
            ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_details.product_id')
            ->whereBetween('transactions.created_at', [$from, $to])
            ->select('products.name as product_name')
            ->selectRaw('SUM(transaction_details.qty) as total_qty')
            ->groupBy('transaction_details.product_id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();
    }
}