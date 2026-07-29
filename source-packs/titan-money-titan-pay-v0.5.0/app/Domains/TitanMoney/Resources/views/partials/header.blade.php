<header class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3">
        <div><h1 class="text-xl font-bold text-gray-900">@yield('titan-money-title','Titan Money')</h1><p class="text-sm text-gray-500">@yield('titan-money-subtitle','Financial records and receivables')</p></div>
        <nav class="flex flex-wrap gap-2 text-sm">
            <a class="px-3 py-2 rounded-lg {{ request()->routeIs('titanmoney.dashboard')?'bg-purple-100 text-purple-800':'text-gray-600 hover:bg-gray-100' }}" href="{{ route('titanmoney.dashboard') }}">Overview</a>
            <a class="px-3 py-2 rounded-lg {{ request()->routeIs('titanmoney.invoices.*')?'bg-purple-100 text-purple-800':'text-gray-600 hover:bg-gray-100' }}" href="{{ route('titanmoney.invoices.index') }}">Invoices</a>
            <a class="px-3 py-2 rounded-lg {{ request()->routeIs('titanmoney.customers.*')?'bg-purple-100 text-purple-800':'text-gray-600 hover:bg-gray-100' }}" href="{{ route('titanmoney.customers.index') }}">Customers</a>
            <a class="px-3 py-2 rounded-lg {{ request()->routeIs('titanmoney.products.*')?'bg-purple-100 text-purple-800':'text-gray-600 hover:bg-gray-100' }}" href="{{ route('titanmoney.products.index') }}">Products</a>
            <a class="px-3 py-2 rounded-lg {{ request()->routeIs('titanmoney.recurring.*')?'bg-purple-100 text-purple-800':'text-gray-600 hover:bg-gray-100' }}" href="{{ route('titanmoney.recurring.index') }}">Recurring</a>
            <a class="px-3 py-2 rounded-lg {{ request()->routeIs('titanmoney.payments.*')?'bg-purple-100 text-purple-800':'text-gray-600 hover:bg-gray-100' }}" href="{{ route('titanmoney.payments.index') }}">Payments</a>
            <a class="px-3 py-2 rounded-lg {{ request()->routeIs('titanmoney.automation.*')?'bg-purple-100 text-purple-800':'text-gray-600 hover:bg-gray-100' }}" href="{{ route('titanmoney.automation.index') }}">Agents</a>
            <a class="px-3 py-2 rounded-lg bg-gray-900 text-white" href="{{ route('titanpay.dashboard') }}">Titan Pay</a>
        </nav>
    </div>
</header>
