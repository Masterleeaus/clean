<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Titan\Tenancy\ActiveCompanyContext;
use Illuminate\Contracts\View\View;

final class DashboardController extends Controller
{
    public function index(ActiveCompanyContext $context): View
    {
        $companyId = $context->companyId;
        $companyUsers = User::query()->whereHas('companyMemberships', static function ($query) use ($companyId): void {
            $query->where('company_id', $companyId)->where('status', 'active');
        });

        $totalUsers = (clone $companyUsers)->count();
        $totalConversations = Conversation::query()->forCompany($companyId)->count();
        $totalMessages = Message::query()
            ->whereHas('conversation', static fn ($query) => $query->forCompany($companyId))
            ->count();
        $activeToday = (clone $companyUsers)->whereDate('last_seen_at', today())->count();

        $recentUsers = (clone $companyUsers)->latest()->take(5)->get();
        $recentConversations = Conversation::query()
            ->forCompany($companyId)
            ->withCount('participants')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', [
            'company' => auth()->user()->activeCompany,
            'totalUsers' => $totalUsers,
            'totalConversations' => $totalConversations,
            'totalMessages' => $totalMessages,
            'activeToday' => $activeToday,
            'recentUsers' => $recentUsers,
            'recentConversations' => $recentConversations,
        ]);
    }
}
