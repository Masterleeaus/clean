@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600 mt-2">Welcome back, {{ auth()->user()->name }}!</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
            
            <!-- Total Users -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalUsers }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                    </div>
                </div>
            </div>

            <!-- Total Conversations -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Conversations</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalConversations }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="message-square" class="w-6 h-6 text-primary"></i>
                    </div>
                </div>
            </div>

            <!-- Total Messages -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Messages</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalMessages }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="message-circle" class="w-6 h-6 text-purple-600"></i>
                    </div>
                </div>
            </div>

            <!-- Active Today -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Today</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $activeToday }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="activity" class="w-6 h-6 text-green-600"></i>
                    </div>
                </div>
            </div>

        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
            
            <!-- Recent Users -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Users</h2>
                <div class="space-y-3">
                    @forelse($recentUsers as $user)
                    <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg transition">
                        <img src="{{ avatar($user->name) }}" 
                             alt="{{ $user->name }}" 
                             class="w-10 h-10 rounded-full">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                        <span class="text-xs text-gray-400">{{ $user->created_at->diffForHumans() }}</span>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 text-center py-4">No users yet</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Conversations -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Conversations</h2>
                <div class="space-y-3">
                    @forelse($recentConversations as $conversation)
                    <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg transition">
                        <div class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center">
                            <i data-lucide="{{ $conversation->type === 'group' ? 'users' : 'user' }}" class="w-5 h-5 text-white"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $conversation->name ?? 'P2P Chat' }}</p>
                            <p class="text-xs text-gray-500">{{ ucfirst($conversation->type) }} • {{ $conversation->participants_count }} participants</p>
                        </div>
                        <span class="text-xs text-gray-400">{{ $conversation->created_at->diffForHumans() }}</span>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 text-center py-4">No conversations yet</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
