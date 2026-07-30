<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Titan\Tenancy\ActiveCompanyContext;
use App\Titan\Tenancy\CompanyContextResolver;
use App\Titan\Vault\Vault;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class AIAssistantService
{
    public function __construct(
        private readonly Vault $vault,
        private readonly CompanyContextResolver $contexts,
    ) {}

    /** @param array<int,array{role:string,content:string}> $messages */
    public function chat(array $messages, array $options = []): array
    {
        try {
            $configuration = $this->configuration();
            $messages = $this->normaliseMessages($messages);
            if ($messages === []) {
                return $this->failure('AI_INVALID_REQUEST', 'At least one valid message is required.');
            }

            return match ($configuration['provider']) {
                'gemini' => $this->chatWithGemini($messages, $configuration, $options),
                'openai', 'openrouter' => $this->chatWithOpenAiFormat($messages, $configuration, $options),
                default => $this->failure('AI_NOT_CONFIGURED', 'The configured AI provider is not supported.'),
            };
        } catch (ConnectionException) {
            return $this->failure('AI_PROVIDER_UNAVAILABLE', 'The AI provider could not be reached.');
        } catch (RuntimeException $exception) {
            $code = str_starts_with($exception->getMessage(), 'AI_')
                ? $exception->getMessage()
                : 'AI_NOT_CONFIGURED';

            return $this->failure($code, $this->publicMessage($code));
        } catch (Throwable $exception) {
            Log::warning('Titan AI provider request failed.', [
                'exception' => $exception::class,
            ]);

            return $this->failure('AI_PROVIDER_UNAVAILABLE', 'The AI provider is temporarily unavailable.');
        }
    }

    /** @param array<int,array{sender?:string,content?:string}> $messages */
    public function summarizeConversation(array $messages): array
    {
        $lines = [];
        foreach (array_slice($messages, -50) as $message) {
            $content = trim((string) ($message['content'] ?? ''));
            if ($content === '') {
                continue;
            }
            $sender = trim((string) ($message['sender'] ?? 'User')) ?: 'User';
            $lines[] = $sender . ': ' . $this->truncate($content, 2000);
        }

        return $this->chat([
            [
                'role' => 'system',
                'content' => 'Summarise the conversation in two or three concise paragraphs. Separate observed decisions and action items from uncertainty. Do not reveal private reasoning.',
            ],
            [
                'role' => 'user',
                'content' => "Summarise this conversation:\n\n" . implode("\n", $lines),
            ],
        ]);
    }

    /** @param array<int,array{sender?:string,content?:string}> $messages */
    public function generateReplySuggestions(array $messages): array
    {
        $lines = [];
        foreach (array_slice($messages, -20) as $message) {
            $content = trim((string) ($message['content'] ?? ''));
            if ($content !== '') {
                $lines[] = (string) ($message['sender'] ?? 'User') . ': ' . $this->truncate($content, 1200);
            }
        }

        $result = $this->chat([
            [
                'role' => 'system',
                'content' => 'Return exactly three short, natural reply suggestions as a JSON array of strings. Return no markdown and no explanation.',
            ],
            [
                'role' => 'user',
                'content' => "Suggest replies for this conversation:\n\n" . implode("\n", $lines),
            ],
        ]);

        if (! ($result['success'] ?? false)) {
            return $result;
        }

        $decoded = json_decode(trim((string) ($result['message'] ?? '')), true);
        if (! is_array($decoded)) {
            return $this->failure('AI_INVALID_RESPONSE', 'The AI provider returned an invalid response.');
        }

        $suggestions = [];
        foreach (array_slice($decoded, 0, 3) as $suggestion) {
            if (is_string($suggestion) && trim($suggestion) !== '') {
                $suggestions[] = $this->truncate(trim($suggestion), 500);
            }
        }

        if (count($suggestions) !== 3) {
            return $this->failure('AI_INVALID_RESPONSE', 'The AI provider returned an invalid response.');
        }

        return ['success' => true, 'suggestions' => $suggestions];
    }

    /** @return array{provider:string,model:string,key:string,endpoint:string,allowed_hosts:array<int,string>} */
    private function configuration(): array
    {
        $context = $this->context();
        $company = Company::query()->find($context->companyId);
        $settings = is_array($company?->settings) ? $company->settings : [];
        $ai = is_array($settings['ai'] ?? null) ? $settings['ai'] : [];

        $provider = strtolower(trim((string) ($ai['provider'] ?? '')));
        $providerConfig = config("services.ai.providers.{$provider}");
        $reference = trim((string) ($ai['credential_reference'] ?? ''));
        if ($provider === '' || ! is_array($providerConfig) || $reference === '') {
            throw new RuntimeException('AI_NOT_CONFIGURED');
        }

        $model = trim((string) ($ai['model'] ?? $providerConfig['default_model'] ?? ''));
        if ($model === '' || preg_match('/^[A-Za-z0-9._:\/-]{1,160}$/', $model) !== 1) {
            throw new RuntimeException('AI_NOT_CONFIGURED');
        }

        $endpoint = trim((string) ($providerConfig['endpoint'] ?? ''));
        $allowedHosts = array_values(array_filter(array_map('strval', (array) ($providerConfig['allowed_hosts'] ?? []))));
        $this->assertEndpointAllowed($endpoint, $allowedHosts);

        return [
            'provider' => $provider,
            'model' => $model,
            'key' => $this->vault->resolve($context->companyId, $reference, $context->userId),
            'endpoint' => $endpoint,
            'allowed_hosts' => $allowedHosts,
        ];
    }

    private function context(): ActiveCompanyContext
    {
        if (app()->bound(ActiveCompanyContext::class)) {
            return app(ActiveCompanyContext::class);
        }

        $context = $this->contexts->resolve(request());
        if (! $context instanceof ActiveCompanyContext) {
            throw new RuntimeException('AI_NOT_CONFIGURED');
        }

        return $context;
    }

    private function chatWithOpenAiFormat(array $messages, array $configuration, array $options): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . $configuration['key'],
            'Content-Type' => 'application/json',
        ];
        if ($configuration['provider'] === 'openrouter') {
            $headers['HTTP-Referer'] = (string) config('app.url');
            $headers['X-Title'] = (string) config('app.name', 'Titan Zero');
        }

        $response = Http::acceptJson()
            ->withHeaders($headers)
            ->timeout($this->timeout())
            ->post($configuration['endpoint'], [
                'model' => $configuration['model'],
                'messages' => $messages,
                'temperature' => $this->temperature($options),
                'max_tokens' => $this->maxTokens($options),
            ]);

        if ($failure = $this->providerFailure($response)) {
            return $failure;
        }

        $message = data_get($response->json(), 'choices.0.message.content');
        if (! is_string($message) || trim($message) === '') {
            return $this->failure('AI_INVALID_RESPONSE', 'The AI provider returned an invalid response.');
        }

        return [
            'success' => true,
            'message' => trim($message),
            'usage' => is_array($response->json('usage')) ? $response->json('usage') : null,
        ];
    }

    private function chatWithGemini(array $messages, array $configuration, array $options): array
    {
        $system = [];
        $contents = [];
        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $system[] = $message['content'];
                continue;
            }
            $contents[] = [
                'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $message['content']]],
            ];
        }

        $endpoint = str_replace('{model}', rawurlencode($configuration['model']), $configuration['endpoint']);
        $this->assertEndpointAllowed($endpoint, $configuration['allowed_hosts']);
        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $this->temperature($options),
                'maxOutputTokens' => $this->maxTokens($options),
            ],
        ];
        if ($system !== []) {
            $payload['systemInstruction'] = ['parts' => [['text' => implode("\n\n", $system)]]];
        }

        $response = Http::acceptJson()
            ->withHeaders(['x-goog-api-key' => $configuration['key']])
            ->timeout($this->timeout())
            ->post($endpoint, $payload);

        if ($failure = $this->providerFailure($response)) {
            return $failure;
        }

        $message = data_get($response->json(), 'candidates.0.content.parts.0.text');
        if (! is_string($message) || trim($message) === '') {
            return $this->failure('AI_INVALID_RESPONSE', 'The AI provider returned an invalid response.');
        }

        return [
            'success' => true,
            'message' => trim($message),
            'usage' => is_array($response->json('usageMetadata')) ? $response->json('usageMetadata') : null,
        ];
    }

    private function providerFailure(Response $response): ?array
    {
        if ($response->successful()) {
            return null;
        }

        if (in_array($response->status(), [401, 403], true)) {
            return $this->failure('AI_PROVIDER_REJECTED', 'The AI provider rejected the configured credential.');
        }

        if ($response->status() === 429) {
            return $this->failure('AI_PROVIDER_RATE_LIMITED', 'The AI provider rate limit was reached.');
        }

        return $this->failure('AI_PROVIDER_UNAVAILABLE', 'The AI provider is temporarily unavailable.');
    }

    /** @param array<int,array<string,mixed>> $messages @return array<int,array{role:string,content:string}> */
    private function normaliseMessages(array $messages): array
    {
        $normalised = [];
        foreach (array_slice($messages, -60) as $message) {
            $role = strtolower(trim((string) ($message['role'] ?? '')));
            $content = trim((string) ($message['content'] ?? ''));
            if (! in_array($role, ['system', 'user', 'assistant'], true) || $content === '') {
                continue;
            }
            $normalised[] = ['role' => $role, 'content' => $this->truncate($content, 12000)];
        }

        return $normalised;
    }

    private function assertEndpointAllowed(string $endpoint, array $allowedHosts): void
    {
        $parts = parse_url($endpoint);
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (($parts['scheme'] ?? null) !== 'https' || $host === '' || ! in_array($host, $allowedHosts, true)) {
            throw new RuntimeException('AI_NOT_CONFIGURED');
        }
    }

    private function timeout(): int
    {
        return max(5, min(120, (int) config('services.ai.timeout_seconds', 30)));
    }

    private function maxTokens(array $options): int
    {
        return max(64, min(4000, (int) ($options['max_tokens'] ?? config('services.ai.max_tokens', 700))));
    }

    private function temperature(array $options): float
    {
        return max(0.0, min(1.5, (float) ($options['temperature'] ?? config('services.ai.temperature', 0.4))));
    }

    private function truncate(string $value, int $length): string
    {
        return function_exists('mb_substr')
            ? \mb_substr($value, 0, $length)
            : substr($value, 0, $length);
    }

    private function failure(string $code, string $message): array
    {
        return ['success' => false, 'error' => ['code' => $code, 'message' => $message]];
    }

    private function publicMessage(string $code): string
    {
        return match ($code) {
            'AI_NOT_CONFIGURED' => 'AI is not configured for the active company.',
            default => 'The AI provider is temporarily unavailable.',
        };
    }
}
