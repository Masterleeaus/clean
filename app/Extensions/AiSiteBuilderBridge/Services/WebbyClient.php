<?php

declare(strict_types=1);

namespace App\Extensions\AiSiteBuilderBridge\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

final class WebbyClient
{
    public function __construct(private readonly HmacSigner $signer) {}

    public function provision(array $payload): array
    {
        return $this->request('POST', '/api/integrations/titan-zero/projects', $payload);
    }

    public function status(string $externalProjectId): array
    {
        return $this->request('GET', '/api/integrations/titan-zero/projects/' . rawurlencode($externalProjectId));
    }

    private function request(string $method, string $path, array $payload = []): array
    {
        if (! config('ai-site-builder.enabled')) {
            throw new RuntimeException('AI site builder integration is disabled.');
        }

        $baseUrl = (string) config('ai-site-builder.base_url');
        if ($baseUrl === '') {
            throw new RuntimeException('AI site builder base URL is not configured.');
        }

        $body = $payload === [] ? '' : json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $timestamp = (string) now()->timestamp;
        $nonce = (string) Str::uuid();
        $secret = (string) config('ai-site-builder.shared_secret');

        $response = $this->http()
            ->withHeaders([
                'X-Titan-Client' => (string) config('ai-site-builder.client_id'),
                'X-Titan-Timestamp' => $timestamp,
                'X-Titan-Nonce' => $nonce,
                'X-Titan-Signature' => $this->signer->sign($method, $path, $body, $timestamp, $nonce, $secret),
            ])
            ->withBody($body, 'application/json')
            ->send($method, $baseUrl . $path);

        if (! $response->successful()) {
            throw new RuntimeException('Webby request failed with HTTP ' . $response->status() . '.');
        }

        return $response->json();
    }

    private function http(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->timeout(max(5, (int) config('ai-site-builder.timeout', 20)))
            ->retry(2, 250, throw: false);
    }
}
