@php
    $currentUser = auth()->user();
    $isPlatformAdmin = $currentUser && $currentUser->email === config('app.admin_email');
    $hasActiveCompany = $currentUser && (int) $currentUser->active_company_id > 0;
    $activeCompany = $hasActiveCompany ? $currentUser->activeCompany : null;
@endphp

<div class="mini-sidebar bg-white border-r border-gray-200 flex flex-col items-center py-4">
    <div class="mb-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center">
            <img src="{{ getImageUrl(setting('logo')) }}" alt="{{ setting('app_name', 'Titan Zero') }}">
        </div>
    </div>

    <div class="w-full px-2 mb-5 text-center">
        <p class="text-[10px] font-semibold text-gray-700 truncate" title="{{ $activeCompany?->name ?? 'No company selected' }}">
            {{ $activeCompany?->name ?? 'Set up company' }}
        </p>
        @if ($hasActiveCompany)
            <p class="text-[9px] uppercase tracking-wide text-gray-400">{{ $currentUser->companyMemberships()->where('company_id', $currentUser->active_company_id)->value('role_key') ?? 'member' }}</p>
        @endif
    </div>

    <nav class="flex-1 flex flex-col space-y-2 w-full px-2">
        @if ($hasActiveCompany)
            <a href="{{ route('chat') }}" class="sidebar-item {{ request()->routeIs('chat') ? 'active' : '' }}">
                <i data-lucide="message-square" class="w-6 h-6"></i>
                <span class="text-xs mt-1">Chat</span>
                <span class="tooltip">Company Chat</span>
            </a>

            <a href="{{ route('titan.operations') }}" class="sidebar-item {{ request()->routeIs('titan.operations') ? 'active' : '' }}">
                <i data-lucide="workflow" class="w-6 h-6"></i>
                <span class="text-xs mt-1">Operate</span>
                <span class="tooltip">Titan Operations</span>
            </a>
        @else
            <a href="{{ route('titan.company.setup') }}" class="sidebar-item {{ request()->routeIs('titan.company.setup*') ? 'active' : '' }}">
                <i data-lucide="building-2" class="w-6 h-6"></i>
                <span class="text-xs mt-1">Company</span>
                <span class="tooltip">Set Up Company</span>
            </a>
        @endif

        <a href="{{ route('settings') }}" class="sidebar-item {{ request()->routeIs('settings') ? 'active' : '' }}">
            <i data-lucide="user-cog" class="w-6 h-6"></i>
            <span class="text-xs mt-1">Profile</span>
            <span class="tooltip">Personal Settings</span>
        </a>

        @if ($isPlatformAdmin)
            <div class="my-1 border-t border-gray-200"></div>

            <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
                <span class="text-xs mt-1">Admin</span>
                <span class="tooltip">Platform Dashboard</span>
            </a>

            <a href="{{ route('users.index') }}" class="sidebar-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i data-lucide="users" class="w-6 h-6"></i>
                <span class="text-xs mt-1">Users</span>
                <span class="tooltip">Platform Users</span>
            </a>

            <a href="{{ route('settings.index') }}" class="sidebar-item {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                <i data-lucide="cog" class="w-6 h-6"></i>
                <span class="text-xs mt-1">System</span>
                <span class="tooltip">System Settings</span>
            </a>
        @endif
    </nav>

    <div class="relative mt-auto">
        <button id="user-menu-btn" class="sidebar-item" type="button" aria-label="Open user menu">
            <img src="{{ avatar($currentUser->name, $currentUser->avatar) }}" alt="{{ $currentUser->name }}"
                class="w-10 h-10 rounded-full ring-2 ring-gray-200">
        </button>

        <div id="user-dropdown" class="dropdown-menu">
            <div class="px-4 py-3 border-b border-gray-200">
                <p class="text-sm font-semibold text-gray-900">{{ $currentUser->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ $currentUser->email }}</p>
                @if ($activeCompany)
                    <p class="text-xs text-primary truncate mt-1">{{ $activeCompany->name }}</p>
                @endif
            </div>
            <a href="{{ route('settings') }}" class="dropdown-item">
                <i data-lucide="settings" class="w-4 h-4"></i>
                <span>Settings</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item w-full text-left text-red-600">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</div>
