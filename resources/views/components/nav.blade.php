<nav class="bg-slate-900 text-white px-4 py-3 flex gap-6">

    <span class="font-semibold">Simple POS</span>

    <a href="{{ route('pos.create') }}"
        class="{{ request()->routeIs('pos.create') ? 'underline font-semibold' : 'hover:underline' }}">

        Kasir

    </a>

    <a href="{{ route('transactions.index') }}"
        class="{{ request()->routeIs('transactions.index') ? 'underline font-semibold' : 'hover:underline' }}">

        Transaksi

    </a>

</nav>