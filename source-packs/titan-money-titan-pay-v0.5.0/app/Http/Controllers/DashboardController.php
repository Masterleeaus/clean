<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = \App\Models\User::count();
        $totalConversations = \App\Models\Conversation::count();
        $totalMessages = \App\Models\Message::count();
        $activeToday = \App\Models\User::whereDate('created_at', today())->count();

        $recentUsers = \App\Models\User::latest()->take(5)->get();
        $recentConversations = \App\Models\Conversation::withCount('participants')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalUsers',
            'totalConversations',
            'totalMessages',
            'activeToday',
            'recentUsers',
            'recentConversations'
        ));
    }
}
