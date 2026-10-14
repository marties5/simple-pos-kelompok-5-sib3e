<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informasi - Simple POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-slate-100 px-4">
    <div class="w-full max-w-md bg-white rounded-md shadow p-6 text-center">
        <h1 class="text-xl font-bold mb-2">Selamat Datang di Simple POS</h1>
        <p class="text-sm text-slate-600 mb-6">Aplikasi manajemen kasir dan inventaris toko modern.</p>
        
        <div class="space-y-2">
            <a href="{{ route('login') }}" class="block w-full bg-slate-900 text-white rounded-md py-2 text-sm font-medium hover:bg-slate-800">
                Masuk ke Aplikasi
            </a>
        </div>
    </div>
</body>
</html>
