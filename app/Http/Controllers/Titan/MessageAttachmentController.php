<?php

declare(strict_types=1);

namespace App\Http\Controllers\Titan;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Titan\Tenancy\ActiveCompanyContext;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class MessageAttachmentController extends Controller
{
    public function __invoke(Request $request, Message $message, ActiveCompanyContext $context): StreamedResponse
    {
        $message->loadMissing('conversation');
        $conversation = $message->conversation;
        abort_unless($conversation !== null && (int) $conversation->company_id === $context->companyId, 404);
        abort_unless($conversation->participants()->where('user_id', $request->user()->id)->exists(), 403);
        abort_unless(is_string($message->attachment_path) && $message->attachment_path !== '', 404);

        $disk = $this->disk();
        abort_unless($disk->exists($message->attachment_path), 404);

        return $disk->download(
            $message->attachment_path,
            $message->attachment_name ?: basename($message->attachment_path),
            ['Content-Type' => $message->attachment_type ?: 'application/octet-stream'],
        );
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::disk((string) config('titan.private_disk', 'local'));
    }
}
