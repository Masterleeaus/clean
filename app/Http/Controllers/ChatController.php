<?php

namespace App\Http\Controllers;

use App\Models\CompanyMembership;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Participant;
use App\Services\AIAssistantService;
use App\Titan\AI\TitanZeroOrchestrator;
use App\Titan\Tenancy\ActiveCompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    public function __construct(
        private readonly TitanZeroOrchestrator $titanZero,
        private readonly AIAssistantService $aiAssistant,
    ) {}
    public function index()
    {
        $user = auth()->user();

        $conversations = Conversation::query()->forCompany($this->companyId())->whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['latestMessage.user', 'users', 'participants'])
            ->get()
            ->map(function ($conversation) use ($user) {
                // Count unread messages: messages not sent by user and not in message_reads for this user
                $unreadCount = $conversation->messages()
                    ->where('user_id', '!=', $user->id)
                    ->whereDoesntHave('reads', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->count();

                // Get other participant for p2p conversations
                $otherParticipant = null;
                if ($conversation->type === 'p2p') {
                    $otherParticipant = $conversation->users->firstWhere('id', '!=', $user->id);
                }

                return [
                    'id' => $conversation->id,
                    'name' => $conversation->name,
                    'type' => $conversation->type,
                    'avatar' => $conversation->avatar,
                    'latest_message' => $conversation->latestMessage,
                    'unread_count' => $unreadCount,
                    'participants' => $conversation->users->map(function ($participant) {
                        return [
                            'id' => $participant->id,
                            'name' => $participant->name,
                            'email' => $participant->email,
                            'avatar' => $participant->avatar,
                            'is_online' => $participant->isOnline(),
                        ];
                    }),
                    'other_participant' => $otherParticipant ? [
                        'id' => $otherParticipant->id,
                        'name' => $otherParticipant->name,
                        'avatar' => $otherParticipant->avatar,
                        'is_online' => $otherParticipant->isOnline(),
                    ] : null,
                ];
            });

        return response()->json($conversations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:p2p,group',
            'name' => 'required_if:type,group|nullable|string|max:255',
            'user_ids' => 'required|array|min:1|max:100',
            'user_ids.*' => 'integer|distinct|exists:users,id',
        ]);

        $userIds = array_values(array_unique(array_map('intval', (array) $validated['user_ids'])));
        if (in_array((int) auth()->id(), $userIds, true)) {
            throw ValidationException::withMessages([
                'user_ids' => ['Do not include yourself; the current user is added automatically.'],
            ]);
        }
        if ($validated['type'] === 'p2p' && count($userIds) !== 1) {
            throw ValidationException::withMessages([
                'user_ids' => ['A private conversation requires exactly one other company member.'],
            ]);
        }

        $this->assertCompanyUsers($userIds);

        if ($validated['type'] === 'p2p') {
            $otherUserId = $userIds[0];
            $currentUserId = (int) auth()->id();

            $existingConversation = Conversation::query()
                ->forCompany($this->companyId())
                ->where('type', 'p2p')
                ->whereHas('participants', static fn ($query) => $query->where('user_id', $currentUserId))
                ->whereHas('participants', static fn ($query) => $query->where('user_id', $otherUserId))
                ->with(['latestMessage.user', 'users', 'participants'])
                ->first();

            if ($existingConversation) {
                return response()->json($this->formatConversation($existingConversation), 200);
            }
        }

        $conversation = DB::transaction(function () use ($validated, $userIds): Conversation {
            $conversation = Conversation::create([
                'company_id' => $this->companyId(),
                'name' => $validated['type'] === 'group' ? trim((string) $validated['name']) : null,
                'type' => $validated['type'],
                'avatar' => null,
            ]);

            foreach (array_merge($userIds, [(int) auth()->id()]) as $userId) {
                Participant::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                ]);
            }

            return $conversation;
        });

        $conversation->load(['latestMessage.user', 'users', 'participants']);
        broadcast(new \App\Events\ConversationCreated($conversation))->toOthers();

        return response()->json($this->formatConversation($conversation), 201);
    }

    public function show($id)
    {
        $conversation = Conversation::query()->forCompany($this->companyId())->with(['messages.user', 'users'])->findOrFail($id);

        // Check if user is participant
        if (! $conversation->users->contains(auth()->id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($conversation);
    }

    public function getMessages($id)
    {
        $conversation = Conversation::query()->forCompany($this->companyId())->findOrFail($id);

        // Check if user is participant
        if (! $conversation->users->contains(auth()->id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with(['user', 'reads.user'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'conversation_id' => $message->conversation_id,
                    'user_id' => $message->user_id,
                    'message' => $message->message,
                    'attachment_url' => $message->attachment_url,
                    'attachment_name' => $message->attachment_name,
                    'attachment_type' => $message->attachment_type,
                    'attachment_size' => $message->attachment_size,
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                    'user' => $message->user,
                    'read_by' => $message->reads->map(function ($read) {
                        return [
                            'user_id' => $read->user_id,
                            'user_name' => $read->user->name,
                            'read_at' => $read->read_at,
                        ];
                    }),
                ];
            });

        // Mark messages as read
        $participant = Participant::where('conversation_id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if ($participant) {
            $participant->update(['last_read_at' => now()]);
        }

        return response()->json($messages);
    }

    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,txt,csv,doc,docx,xls,xlsx,zip'
        ]);

        $conversation = Conversation::query()->forCompany($this->companyId())->findOrFail($id);

        // Check if user is participant
        if (! $conversation->users->contains(auth()->id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messageData = [
            'conversation_id' => $id,
            'user_id' => auth()->id(),
            'message' => $request->message ?? '',
        ];

        // Handle file attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $extension = strtolower((string) $file->getClientOriginalExtension());
            $fileName = (string) Str::uuid() . ($extension !== '' ? '.' . $extension : '');
            $directory = 'companies/' . $this->companyId() . '/conversations/' . $conversation->id . '/attachments';
            $filePath = $file->storeAs($directory, $fileName, (string) config('titan.private_disk', 'local'));

            $messageData['attachment_path'] = $filePath;
            $messageData['attachment_name'] = $file->getClientOriginalName();
            $messageData['attachment_type'] = $file->getMimeType();
            $messageData['attachment_size'] = $file->getSize();
        }

        $message = Message::create($messageData);

        $message->load('user');
        $message->append('attachment_url');

        // Broadcast message
        broadcast(new \App\Events\MessageSent($message))->toOthers();

        return response()->json($message, 201);
    }

    public function typing(Request $request, $id)
    {
        $conversation = Conversation::query()->forCompany($this->companyId())->findOrFail($id);

        // Check if user is participant
        if (! $conversation->users->contains(auth()->id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        broadcast(new \App\Events\UserTyping($id, auth()->user()))->toOthers();

        return response()->json(['status' => 'typing event sent']);
    }

    public function markAsRead($id)
    {
        $participant = Participant::query()
            ->where('conversation_id', $id)
            ->where('user_id', auth()->id())
            ->whereHas('conversation', fn ($query) => $query->forCompany($this->companyId()))
            ->firstOrFail();

        $participant->update(['last_read_at' => now()]);

        return response()->json(['status' => 'marked as read']);
    }

    public function getUsers()
    {
        $users = \App\Models\User::query()
            ->where('id', '!=', auth()->id())
            ->whereHas('companyMemberships', function ($query): void {
                $query->where('company_id', $this->companyId())->where('status', 'active');
            })
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'is_online' => $user->isOnline(),
                ];
            });

        return response()->json($users);
    }

    public function markMessageAsRead($id)
    {
        $message = Message::query()
            ->whereHas('conversation', fn ($query) => $query->forCompany($this->companyId()))
            ->findOrFail($id);
        $conversation = $message->conversation;

        // Check if user is participant
        if (! $conversation->users->contains(auth()->id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Don't mark own messages as read
        if ($message->user_id === auth()->id()) {
            return response()->json(['status' => 'own message'], 200);
        }

        // Mark message as read (will be ignored if already read due to unique constraint)
        try {
            \App\Models\MessageRead::create([
                'message_id' => $id,
                'user_id' => auth()->id(),
                'read_at' => now(),
            ]);

            // Broadcast read receipt to message sender
            broadcast(new \App\Events\MessageRead($id, auth()->id(), $conversation->id))->toOthers();

            // Calculate updated unread count for current user
            $unreadCount = $conversation->messages()
                ->where('user_id', '!=', auth()->id())
                ->whereDoesntHave('reads', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->count();

            return response()->json([
                'status' => 'marked as read',
                'unread_count' => $unreadCount,
            ], 200);
        } catch (\Exception $e) {
            // Already marked as read - still return current unread count
            $unreadCount = $conversation->messages()
                ->where('user_id', '!=', auth()->id())
                ->whereDoesntHave('reads', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->count();

            return response()->json([
                'status' => 'already read',
                'unread_count' => $unreadCount,
            ], 200);
        }
    }

    public function update(Request $request, $id)
    {
        $conversation = Conversation::query()->forCompany($this->companyId())->findOrFail($id);

        // Check if user is participant
        if (! $conversation->users->contains(auth()->id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Only allow updating group chats
        if ($conversation->type !== 'group') {
            return response()->json(['error' => 'Cannot update P2P conversation'], 400);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $data = ['name' => $request->name];

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($conversation->avatar && \Storage::disk('public')->exists($conversation->avatar)) {
                \Storage::disk('public')->delete($conversation->avatar);
            }

            $file = $request->file('avatar');
            $fileName = time().'_group_'.$file->getClientOriginalName();
            $filePath = $file->storeAs('avatars', $fileName, 'public');
            $data['avatar'] = $filePath;
        }

        $conversation->update($data);

        return response()->json($conversation);
    }

    public function addParticipants(Request $request, $id)
    {
        $conversation = Conversation::query()->forCompany($this->companyId())->findOrFail($id);

        // Check if user is participant
        if (! $conversation->users->contains(auth()->id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($conversation->type !== 'group') {
            return response()->json(['error' => 'Cannot add participants to P2P conversation'], 400);
        }

        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
        ]);
        $this->assertCompanyUsers((array) $validated['user_ids']);

        $addedUsers = [];
        foreach ($validated['user_ids'] as $userId) {
            // Check if already a participant
            if (! $conversation->users->contains($userId)) {
                Participant::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                ]);
                $addedUsers[] = $userId;
            }
        }

        return response()->json(['message' => 'Participants added successfully', 'added_users' => $addedUsers]);
    }

    public function removeParticipant($id, $userId)
    {
        $conversation = Conversation::query()->forCompany($this->companyId())->findOrFail($id);

        // Check if user is participant
        if (! $conversation->users->contains(auth()->id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($conversation->type !== 'group') {
            return response()->json(['error' => 'Cannot remove participants from P2P conversation'], 400);
        }

        // Prevent removing self (use leave endpoint instead)
        if ((int) $userId === auth()->id()) {
            return response()->json(['error' => 'Use leave endpoint to remove yourself'], 400);
        }

        $participant = Participant::where('conversation_id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($participant) {
            $participant->delete();

            return response()->json(['message' => 'Participant removed successfully']);
        }

        return response()->json(['error' => 'Participant not found'], 404);
    }

    public function leave($id)
    {
        $conversation = Conversation::query()->forCompany($this->companyId())->findOrFail($id);

        // Check if user is participant
        if (! $conversation->users->contains(auth()->id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($conversation->type !== 'group') {
            return response()->json(['error' => 'Cannot leave P2P conversation'], 400);
        }

        $participant = Participant::where('conversation_id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if ($participant) {
            $participant->delete();

            // If no participants left, delete conversation?
            // Or maybe keep it archived. For now, let's just leave it.
            // If we want to auto-delete empty groups:
            if ($conversation->participants()->count() === 0) {
                $conversation->delete();
            }

            return response()->json(['message' => 'Left conversation successfully']);
        }

        return response()->json(['error' => 'Not a participant'], 404);
    }

    public function destroy($id)
    {
        $conversation = Conversation::query()->forCompany($this->companyId())->findOrFail($id);

        // Check if user is participant
        if (! $conversation->users->contains(auth()->id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete all attachment files from storage
        $messages = $conversation->messages()->whereNotNull('attachment_path')->get();
        foreach ($messages as $message) {
            if ($message->attachment_path && Storage::disk((string) config('titan.private_disk', 'local'))->exists($message->attachment_path)) {
                Storage::disk((string) config('titan.private_disk', 'local'))->delete($message->attachment_path);
            }
        }

        // Delete all messages in the conversation
        $conversation->messages()->delete();

        // Delete all participants
        $conversation->participants()->delete();

        // Delete the conversation itself
        $conversation->delete();

        return response()->json(['message' => 'Conversation deleted successfully'], 200);
    }

    /**
     * Send a company-scoped message through Titan Zero.
     */
    public function aiAssistantMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:12000',
            'conversation_id' => 'nullable|integer|exists:conversations,id',
            'confirmation_id' => 'nullable|string|max:160',
        ]);

        $context = app(ActiveCompanyContext::class);
        $user = $request->user();
        $createdConversation = false;

        if (isset($validated['conversation_id'])) {
            $conversation = Conversation::query()
                ->forCompany($context->companyId)
                ->where('type', 'ai_assistant')
                ->findOrFail((int) $validated['conversation_id']);

            if (! $conversation->participants()->where('user_id', $user->id)->exists()) {
                return response()->json($this->safeError('CONVERSATION_ACCESS_DENIED', 'You cannot access this Titan Zero conversation.'), 403);
            }
        } else {
            $conversation = DB::transaction(function () use ($context, $user): Conversation {
                $conversation = Conversation::create([
                    'company_id' => $context->companyId,
                    'name' => 'Titan Zero',
                    'type' => 'ai_assistant',
                ]);
                Participant::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $user->id,
                ]);

                return $conversation;
            });
            $createdConversation = true;
        }

        $messages = $conversation->messages()
            ->orderBy('id')
            ->limit(60)
            ->get()
            ->map(static fn ($message): array => [
                'role' => $message->user_id === null ? 'assistant' : 'user',
                'content' => (string) $message->message,
            ])
            ->all();
        $messages[] = ['role' => 'user', 'content' => $validated['message']];

        $result = $this->titanZero->respond(
            $conversation,
            $messages,
            isset($validated['confirmation_id']) ? trim((string) $validated['confirmation_id']) : null,
        );
        if (! ($result['success'] ?? false)) {
            if ($createdConversation) {
                DB::transaction(function () use ($conversation): void {
                    $conversation->participants()->delete();
                    $conversation->delete();
                });
            }

            $error = is_array($result['error'] ?? null)
                ? $result['error']
                : ['code' => 'TITAN_ZERO_UNAVAILABLE', 'message' => 'Titan Zero is temporarily unavailable.'];

            return response()->json(['error' => $error], $this->aiErrorStatus((string) ($error['code'] ?? '')));
        }

        [$userMessage, $aiMessage] = DB::transaction(function () use ($conversation, $user, $validated, $result): array {
            $userMessage = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'message' => $validated['message'],
            ]);
            $aiMessage = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => null,
                'message' => (string) $result['message'],
            ]);

            return [$userMessage, $aiMessage];
        });

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'user_message' => $userMessage,
            'ai_message' => $aiMessage,
            'tool_result' => $result['tool_result'] ?? null,
        ]);
    }

    /**
     * Return the active company's Titan Zero conversation for this user.
     */
    public function getAIConversation(Request $request)
    {
        $context = app(ActiveCompanyContext::class);
        $conversation = Conversation::query()
            ->forCompany($context->companyId)
            ->where('type', 'ai_assistant')
            ->whereHas('participants', static fn ($query) => $query->where('user_id', $request->user()->id))
            ->with(['messages.user'])
            ->latest('id')
            ->first();

        return response()->json(['conversation' => $conversation]);
    }

    /**
     * Generate a safe summary inside the active company boundary.
     */
    public function aiSummary(Request $request, $id)
    {
        $conversation = $this->authorisedCompanyConversation($request, (int) $id);
        if ($conversation instanceof \Illuminate\Http\JsonResponse) {
            return $conversation;
        }

        $messages = $conversation->messages()
            ->with('user:id,name')
            ->latest('id')
            ->limit(50)
            ->get()
            ->reverse()
            ->map(static fn ($message): array => [
                'sender' => $message->user?->name ?? 'Titan Zero',
                'content' => (string) $message->message,
            ])
            ->values()
            ->all();

        if ($messages === []) {
            return response()->json($this->safeError('CONVERSATION_EMPTY', 'There are no messages to summarise.'), 422);
        }

        $result = $this->aiAssistant->summarizeConversation($messages);
        if (! ($result['success'] ?? false)) {
            $error = $result['error'] ?? ['code' => 'AI_PROVIDER_UNAVAILABLE', 'message' => 'The AI provider is temporarily unavailable.'];
            return response()->json(['error' => $error], $this->aiErrorStatus((string) ($error['code'] ?? '')));
        }

        return response()->json(['success' => true, 'summary' => $result['message']]);
    }

    /**
     * Generate company-scoped reply suggestions without exposing provider errors.
     */
    public function aiSuggestions(Request $request, $id)
    {
        $conversation = $this->authorisedCompanyConversation($request, (int) $id);
        if ($conversation instanceof \Illuminate\Http\JsonResponse) {
            return $conversation;
        }

        $messages = $conversation->messages()
            ->with('user:id,name')
            ->latest('id')
            ->limit(10)
            ->get()
            ->reverse()
            ->map(static fn ($message): array => [
                'sender' => $message->user?->name ?? 'Titan Zero',
                'content' => (string) $message->message,
            ])
            ->values()
            ->all();

        if ($messages === []) {
            return response()->json($this->safeError('CONVERSATION_EMPTY', 'There are no messages for reply suggestions.'), 422);
        }

        $result = $this->aiAssistant->generateReplySuggestions($messages);
        if (! ($result['success'] ?? false)) {
            $error = $result['error'] ?? ['code' => 'AI_PROVIDER_UNAVAILABLE', 'message' => 'The AI provider is temporarily unavailable.'];
            return response()->json(['error' => $error], $this->aiErrorStatus((string) ($error['code'] ?? '')));
        }

        return response()->json(['success' => true, 'suggestions' => $result['suggestions']]);
    }

    private function authorisedCompanyConversation(Request $request, int $id): Conversation|\Illuminate\Http\JsonResponse
    {
        $context = app(ActiveCompanyContext::class);
        $conversation = Conversation::query()->forCompany($context->companyId)->find($id);
        if ($conversation === null) {
            return response()->json($this->safeError('CONVERSATION_NOT_FOUND', 'The conversation was not found in the active company.'), 404);
        }
        if (! $conversation->participants()->where('user_id', $request->user()->id)->exists()) {
            return response()->json($this->safeError('CONVERSATION_ACCESS_DENIED', 'You cannot access this conversation.'), 403);
        }

        return $conversation;
    }

    /** @return array{error:array{code:string,message:string}} */
    private function safeError(string $code, string $message): array
    {
        return ['error' => ['code' => $code, 'message' => $message]];
    }

    private function aiErrorStatus(string $code): int
    {
        return match ($code) {
            'AI_NOT_CONFIGURED', 'TENANT_CONTEXT_REQUIRED' => 409,
            'TITAN_TOOL_CONFIRMATION_REQUIRED' => 428,
            'TITAN_TOOL_PERMISSION_DENIED', 'CONVERSATION_ACCESS_DENIED' => 403,
            'AI_INVALID_REQUEST', 'AI_INVALID_RESPONSE' => 422,
            'AI_PROVIDER_RATE_LIMITED' => 429,
            default => 503,
        };
    }

    private function companyId(): int
    {
        return app(ActiveCompanyContext::class)->companyId;
    }

    /** @param array<int,mixed> $userIds */
    private function assertCompanyUsers(array $userIds): void
    {
        $ids = array_values(array_unique(array_map('intval', $userIds)));
        $allowed = CompanyMembership::query()
            ->where('company_id', $this->companyId())
            ->where('status', 'active')
            ->whereIn('user_id', $ids)
            ->pluck('user_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        sort($ids);
        sort($allowed);
        if ($ids !== $allowed) {
            throw ValidationException::withMessages([
                'user_ids' => ['Every chat participant must belong to the active company.'],
            ]);
        }
    }

    /**
     * Format conversation data consistently for API responses
     */
    private function formatConversation($conversation)
    {
        $user = auth()->user();

        // Count unread messages: messages not sent by user and not in message_reads for this user
        $unreadCount = $conversation->messages()
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('reads', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        // Get other participant for p2p conversations
        $otherParticipant = null;
        if ($conversation->type === 'p2p') {
            $otherParticipant = $conversation->users->firstWhere('id', '!=', $user->id);
        }

        return [
            'id' => $conversation->id,
            'name' => $conversation->name,
            'type' => $conversation->type,
            'avatar' => $conversation->avatar,
            'latest_message' => $conversation->latestMessage,
            'unread_count' => $unreadCount,
            'participants' => $conversation->users->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'email' => $participant->email,
                    'avatar' => $participant->avatar,
                    'is_online' => $participant->isOnline(),
                ];
            }),
            'other_participant' => $otherParticipant ? [
                'id' => $otherParticipant->id,
                'name' => $otherParticipant->name,
                'avatar' => $otherParticipant->avatar,
                'is_online' => $otherParticipant->isOnline(),
            ] : null,
        ];
    }
}
