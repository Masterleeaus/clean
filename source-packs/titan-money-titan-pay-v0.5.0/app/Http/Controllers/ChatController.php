<?php

namespace App\Http\Controllers;

use App\Domains\Company\Models\CompanyMembership;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanZero\Chat\TitanOperationalToolRouter;
use App\Events\ConversationCreated;
use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageRead as MessageReadModel;
use App\Models\Participant;
use App\Models\User;
use App\Services\AIAssistantService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $companyId = $this->companyId();

        $conversations = $this->companyConversations($companyId)
            ->whereHas('participants', fn (Builder $query) => $query->where('user_id', $user->id))
            ->with(['latestMessage.user', 'users', 'participants'])
            ->latest('updated_at')
            ->get()
            ->map(fn (Conversation $conversation) => $this->formatConversation($conversation));

        return response()->json($conversations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:p2p,group'],
            'name' => ['required_if:type,group', 'nullable', 'string', 'max:255'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);

        $user = auth()->user();
        $companyId = $this->companyId();
        $requestedUserIds = array_values(array_unique(array_map('intval', $validated['user_ids'])));

        if ($validated['type'] === 'p2p' && count($requestedUserIds) !== 1) {
            throw ValidationException::withMessages(['user_ids' => 'A direct conversation requires exactly one other user.']);
        }

        if (in_array((int) $user->id, $requestedUserIds, true)) {
            throw ValidationException::withMessages(['user_ids' => 'You cannot add yourself as the other participant.']);
        }

        $eligible = $this->eligibleCompanyUserIds($requestedUserIds, $companyId);
        if (count($eligible) !== count($requestedUserIds)) {
            throw ValidationException::withMessages(['user_ids' => 'Every participant must be an active member of the current company.']);
        }

        if ($validated['type'] === 'p2p') {
            $otherUserId = $requestedUserIds[0];
            $existingConversation = $this->companyConversations($companyId)
                ->where('type', 'p2p')
                ->whereHas('participants', fn (Builder $query) => $query->where('user_id', $user->id))
                ->whereHas('participants', fn (Builder $query) => $query->where('user_id', $otherUserId))
                ->has('participants', '=', 2)
                ->with(['latestMessage.user', 'users', 'participants'])
                ->first();

            if ($existingConversation) {
                return response()->json($this->formatConversation($existingConversation));
            }
        }

        $conversation = DB::transaction(function () use ($validated, $requestedUserIds, $companyId, $user): Conversation {
            $conversation = Conversation::query()->create([
                'company_id' => $companyId,
                'created_by_user_id' => $user->id,
                'name' => $validated['name'] ?? null,
                'type' => $validated['type'],
                'avatar' => null,
            ]);

            foreach (array_unique([...$requestedUserIds, (int) $user->id]) as $userId) {
                Participant::query()->create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                ]);
            }

            return $conversation;
        });

        $conversation->load(['latestMessage.user', 'users', 'participants']);
        broadcast(new ConversationCreated($conversation))->toOthers();

        return response()->json($this->formatConversation($conversation), 201);
    }

    public function show(int|string $id): JsonResponse
    {
        $conversation = $this->findConversationForUser($id, ['messages.user', 'users']);

        return response()->json($conversation);
    }

    public function getMessages(int|string $id): JsonResponse
    {
        $conversation = $this->findConversationForUser($id);

        $messages = $conversation->messages()
            ->with(['user', 'reads.user'])
            ->oldest('created_at')
            ->get()
            ->map(fn (Message $message) => $this->formatMessage($message));

        Participant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', auth()->id())
            ->update(['last_read_at' => now()]);

        return response()->json($messages);
    }

    public function sendMessage(Request $request, int|string $id): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:10000', 'required_without:attachment'],
            'attachment' => [
                'nullable',
                'file',
                'max:10240',
                'required_without:message',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp,application/pdf,text/plain,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        ]);

        $conversation = $this->findConversationForUser($id);
        $messageData = [
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'message' => trim((string) ($validated['message'] ?? '')),
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->storeAs(
                'chat-attachments/'.$conversation->company_id.'/'.$conversation->id,
                $file->hashName(),
                'local'
            );

            if (! $path) {
                abort(500, 'Unable to store the attachment securely.');
            }

            $messageData['attachment_path'] = $path;
            $messageData['attachment_name'] = $this->normaliseAttachmentName($file->getClientOriginalName());
            $messageData['attachment_type'] = $file->getMimeType();
            $messageData['attachment_size'] = $file->getSize();
        }

        $message = Message::query()->create($messageData);
        $message->load(['user', 'reads.user']);
        $conversation->touch();

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($this->formatMessage($message), 201);
    }

    public function attachmentDownload(int|string $messageId): StreamedResponse
    {
        $message = Message::query()->with('conversation')->findOrFail($messageId);
        $this->assertConversationParticipant($message->conversation);

        abort_unless($message->attachment_path, 404);
        $downloadName = $message->attachment_name ?: basename($message->attachment_path);

        if (Storage::disk('local')->exists($message->attachment_path)) {
            return Storage::disk('local')->download($message->attachment_path, $downloadName);
        }

        // Authenticated compatibility path: migrate legacy public files on first authorised access.
        if (Storage::disk('public')->exists($message->attachment_path)) {
            $contents = Storage::disk('public')->get($message->attachment_path);
            abort_unless(Storage::disk('local')->put($message->attachment_path, $contents), 500, 'Unable to migrate the legacy attachment.');
            Storage::disk('public')->delete($message->attachment_path);

            return Storage::disk('local')->download($message->attachment_path, $downloadName);
        }

        abort(404);
    }

    public function typing(Request $request, int|string $id): JsonResponse
    {
        $conversation = $this->findConversationForUser($id);
        broadcast(new UserTyping($conversation->id, auth()->user()))->toOthers();

        return response()->json(['status' => 'typing event sent']);
    }

    public function markAsRead(int|string $id): JsonResponse
    {
        $conversation = $this->findConversationForUser($id);

        Participant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', auth()->id())
            ->update(['last_read_at' => now()]);

        return response()->json(['status' => 'marked as read']);
    }

    public function getUsers(): JsonResponse
    {
        $companyId = $this->companyId();
        $users = User::query()
            ->whereKeyNot(auth()->id())
            ->whereHas('companyMemberships', function (Builder $query) use ($companyId): void {
                $query->where('company_id', $companyId)->where('is_active', true);
            })
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'is_online' => $user->isOnline(),
            ]);

        return response()->json($users);
    }

    public function markMessageAsRead(int|string $id): JsonResponse
    {
        $message = Message::query()->with('conversation')->findOrFail($id);
        $this->assertConversationParticipant($message->conversation);

        if ((int) $message->user_id === (int) auth()->id()) {
            return response()->json(['status' => 'own message']);
        }

        $read = MessageReadModel::query()->firstOrCreate(
            ['message_id' => $message->id, 'user_id' => auth()->id()],
            ['read_at' => now()]
        );

        if ($read->wasRecentlyCreated) {
            broadcast(new MessageRead($message->id, auth()->id(), $message->conversation_id))->toOthers();
        }

        $unreadCount = $message->conversation->messages()
            ->where('user_id', '!=', auth()->id())
            ->whereDoesntHave('reads', fn (Builder $query) => $query->where('user_id', auth()->id()))
            ->count();

        return response()->json([
            'status' => $read->wasRecentlyCreated ? 'marked as read' : 'already read',
            'unread_count' => $unreadCount,
        ]);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $conversation = $this->findConversationForUser($id);
        $this->assertConversationOwner($conversation);
        abort_unless($conversation->type === 'group', 400, 'Cannot update a direct conversation.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $data = ['name' => $validated['name']];
        if ($request->hasFile('avatar')) {
            if ($conversation->avatar && Storage::disk('public')->exists($conversation->avatar)) {
                Storage::disk('public')->delete($conversation->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars/groups', 'public');
        }

        $conversation->update($data);

        return response()->json($conversation);
    }

    public function addParticipants(Request $request, int|string $id): JsonResponse
    {
        $conversation = $this->findConversationForUser($id);
        $this->assertConversationOwner($conversation);
        abort_unless($conversation->type === 'group', 400, 'Cannot add participants to a direct conversation.');

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);
        $requested = array_values(array_unique(array_map('intval', $validated['user_ids'])));
        $eligible = $this->eligibleCompanyUserIds($requested, $conversation->company_id);

        if (count($requested) !== count($eligible)) {
            throw ValidationException::withMessages(['user_ids' => 'Every participant must be an active member of this company.']);
        }

        $added = [];
        foreach ($eligible as $userId) {
            $participant = Participant::query()->firstOrCreate([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
            ]);
            if ($participant->wasRecentlyCreated) {
                $added[] = $userId;
            }
        }

        return response()->json(['message' => 'Participants added successfully', 'added_users' => $added]);
    }

    public function removeParticipant(int|string $id, int|string $userId): JsonResponse
    {
        $conversation = $this->findConversationForUser($id);
        $this->assertConversationOwner($conversation);
        abort_unless($conversation->type === 'group', 400, 'Cannot remove participants from a direct conversation.');
        abort_if((int) $userId === (int) $conversation->created_by_user_id, 400, 'The group owner cannot be removed.');

        $deleted = Participant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $userId)
            ->delete();

        abort_unless($deleted > 0, 404, 'Participant not found.');

        return response()->json(['message' => 'Participant removed successfully']);
    }

    public function leave(int|string $id): JsonResponse
    {
        $conversation = $this->findConversationForUser($id);
        abort_unless($conversation->type === 'group', 400, 'Cannot leave a direct conversation.');

        DB::transaction(function () use ($conversation): void {
            Participant::query()
                ->where('conversation_id', $conversation->id)
                ->where('user_id', auth()->id())
                ->delete();

            $nextParticipant = $conversation->participants()->oldest('id')->first();
            if (! $nextParticipant) {
                $this->deleteConversationFiles($conversation);
                $conversation->delete();
                return;
            }

            if ((int) $conversation->created_by_user_id === (int) auth()->id()) {
                $conversation->update(['created_by_user_id' => $nextParticipant->user_id]);
            }
        });

        return response()->json(['message' => 'Left conversation successfully']);
    }

    public function destroy(int|string $id): JsonResponse
    {
        $conversation = $this->findConversationForUser($id);
        $this->assertConversationOwner($conversation);
        abort_if($conversation->type === 'p2p', 409, 'Direct conversations cannot be destructively deleted for both participants.');

        DB::transaction(function () use ($conversation): void {
            $this->deleteConversationFiles($conversation);
            $conversation->delete();
        });

        return response()->json(['message' => 'Conversation deleted successfully']);
    }

    public function aiAssistantMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:10000'],
            'conversation_id' => ['nullable', 'integer', 'exists:conversations,id'],
        ]);

        $user = auth()->user();
        $companyId = $this->companyId();

        if (! empty($validated['conversation_id'])) {
            $conversation = $this->companyConversations($companyId)->find($validated['conversation_id']);
            if (! $conversation || $conversation->type !== 'ai_assistant' || ! $conversation->participants()->where('user_id', $user->id)->exists()) {
                throw ValidationException::withMessages(['conversation_id' => 'The requested AI conversation is not available.']);
            }
        } else {
            $conversation = Conversation::query()->create([
                'company_id' => $companyId,
                'created_by_user_id' => $user->id,
                'name' => 'AI Assistant',
                'type' => 'ai_assistant',
            ]);
            Participant::query()->create(['conversation_id' => $conversation->id, 'user_id' => $user->id]);
        }

        $userMessage = Message::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
        ]);
        broadcast(new MessageSent($userMessage))->toOthers();

        $operational = app(TitanOperationalToolRouter::class)->handle(
            app(CurrentCompany::class)->require(),
            $user,
            $conversation->id,
            $validated['message'],
        );
        if ($operational !== null) {
            $aiMessage = Message::query()->create([
                'conversation_id' => $conversation->id,
                'user_id' => null,
                'message' => $operational->message,
                'metadata_json' => array_merge($operational->metadata, [
                    'message_type' => 'operational_result',
                    'success' => $operational->success,
                ]),
            ]);
            $conversation->touch();
            broadcast(new MessageSent($aiMessage))->toOthers();

            return response()->json([
                'success' => $operational->success,
                'conversation_id' => $conversation->id,
                'user_message' => $userMessage,
                'ai_message' => $aiMessage,
                'operational_result' => $operational->metadata,
            ], $operational->httpStatus);
        }

        $messages = $conversation->messages()
            ->with('user')
            ->oldest('created_at')
            ->limit(100)
            ->get()
            ->map(fn (Message $message) => [
                'role' => $message->user_id === $user->id ? 'user' : 'assistant',
                'content' => $message->message,
            ])
            ->all();

        $aiResponse = app(AIAssistantService::class)->chat($messages);
        if (! $aiResponse['success']) {
            return response()->json(['error' => $aiResponse['error'], 'conversation_id' => $conversation->id], 502);
        }

        $aiMessage = Message::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => null,
            'message' => $aiResponse['message'],
        ]);
        $conversation->touch();
        broadcast(new MessageSent($aiMessage))->toOthers();

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'user_message' => $userMessage,
            'ai_message' => $aiMessage,
        ]);
    }

    public function getAIConversation(): JsonResponse
    {
        $conversation = $this->companyConversations($this->companyId())
            ->where('type', 'ai_assistant')
            ->whereHas('participants', fn (Builder $query) => $query->where('user_id', auth()->id()))
            ->with(['messages.user'])
            ->latest('updated_at')
            ->first();

        return response()->json(['conversation' => $conversation]);
    }

    public function aiSummary(Request $request, int|string $id): JsonResponse
    {
        $conversation = $this->findConversationForUser($id);
        $messages = $conversation->messages()
            ->with('user')
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->map(fn (Message $message) => [
                'sender' => $message->user?->name ?? 'AI Assistant',
                'content' => $message->message,
            ])
            ->all();

        abort_if($messages === [], 400, 'No messages to summarize.');
        $result = app(AIAssistantService::class)->summarizeConversation($messages);

        return $result['success']
            ? response()->json(['success' => true, 'summary' => $result['message']])
            : response()->json(['error' => $result['error']], 502);
    }

    public function aiSuggestions(Request $request, int|string $id): JsonResponse
    {
        $conversation = $this->findConversationForUser($id);
        $messages = $conversation->messages()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get()
            ->reverse()
            ->map(fn (Message $message) => [
                'sender' => $message->user?->name ?? 'AI',
                'content' => $message->message,
            ])
            ->all();

        abort_if($messages === [], 400, 'No messages to generate suggestions from.');
        $result = app(AIAssistantService::class)->generateReplySuggestions($messages);

        return $result['success']
            ? response()->json(['suggestions' => $result['suggestions']])
            : response()->json(['error' => $result['error']], 502);
    }

    private function companyId(): string
    {
        return (string) app(CurrentCompany::class)->require()->id;
    }

    private function companyConversations(string $companyId): Builder
    {
        return Conversation::query()->where('company_id', $companyId);
    }

    private function findConversationForUser(int|string $id, array $with = []): Conversation
    {
        $query = $this->companyConversations($this->companyId());
        if ($with !== []) {
            $query->with($with);
        }

        $conversation = $query->findOrFail($id);
        $this->assertConversationParticipant($conversation);

        return $conversation;
    }

    private function assertConversationParticipant(Conversation $conversation): void
    {
        abort_unless(
            $conversation->company_id === $this->companyId()
                && $conversation->participants()->where('user_id', auth()->id())->exists(),
            403,
            'Unauthorized.'
        );
    }

    private function assertConversationOwner(Conversation $conversation): void
    {
        $this->assertConversationParticipant($conversation);
        abort_unless((int) $conversation->created_by_user_id === (int) auth()->id(), 403, 'Only the conversation owner may perform this action.');
    }

    /** @param array<int, int> $userIds @return array<int, int> */
    private function eligibleCompanyUserIds(array $userIds, string $companyId): array
    {
        return CompanyMembership::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function formatMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'user_id' => $message->user_id,
            'message' => $message->message,
            'metadata_json' => $message->metadata_json,
            'attachment_path' => $message->attachment_path ? true : null,
            'attachment_url' => $message->attachment_path ? route('chat.attachments.download', $message->id) : null,
            'attachment_name' => $message->attachment_name,
            'attachment_type' => $message->attachment_type,
            'attachment_size' => $message->attachment_size,
            'created_at' => $message->created_at,
            'updated_at' => $message->updated_at,
            'user' => $message->user,
            'read_by' => $message->reads->map(fn ($read) => [
                'user_id' => $read->user_id,
                'user_name' => $read->user?->name,
                'read_at' => $read->read_at,
            ]),
        ];
    }

    private function formatConversation(Conversation $conversation): array
    {
        $user = auth()->user();
        $unreadCount = $conversation->messages()
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('reads', fn (Builder $query) => $query->where('user_id', $user->id))
            ->count();
        $otherParticipant = $conversation->type === 'p2p'
            ? $conversation->users->firstWhere('id', '!=', $user->id)
            : null;

        return [
            'id' => $conversation->id,
            'name' => $conversation->name,
            'type' => $conversation->type,
            'avatar' => $conversation->avatar,
            'is_owner' => (int) $conversation->created_by_user_id === (int) $user->id,
            'latest_message' => $conversation->latestMessage ? $this->formatMessage($conversation->latestMessage->loadMissing(['user', 'reads.user'])) : null,
            'unread_count' => $unreadCount,
            'participants' => $conversation->users->map(fn (User $participant) => [
                'id' => $participant->id,
                'name' => $participant->name,
                'email' => $participant->email,
                'avatar' => $participant->avatar,
                'is_online' => $participant->isOnline(),
            ]),
            'other_participant' => $otherParticipant ? [
                'id' => $otherParticipant->id,
                'name' => $otherParticipant->name,
                'avatar' => $otherParticipant->avatar,
                'is_online' => $otherParticipant->isOnline(),
            ] : null,
        ];
    }


    private function normaliseAttachmentName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[^\pL\pN._ -]+/u', '_', $name) ?: 'attachment';
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?: $name, " .\t\n\r\0\x0B");

        return mb_substr($name !== '' ? $name : 'attachment', 0, 180);
    }

    private function deleteConversationFiles(Conversation $conversation): void
    {
        $conversation->messages()->whereNotNull('attachment_path')->each(function (Message $message): void {
            if (Storage::disk('local')->exists($message->attachment_path)) {
                Storage::disk('local')->delete($message->attachment_path);
            }
            if (Storage::disk('public')->exists($message->attachment_path)) {
                Storage::disk('public')->delete($message->attachment_path);
            }
        });

        if ($conversation->avatar && Storage::disk('public')->exists($conversation->avatar)) {
            Storage::disk('public')->delete($conversation->avatar);
        }
    }
}
