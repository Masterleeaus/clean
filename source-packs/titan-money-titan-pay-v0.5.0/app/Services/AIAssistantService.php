<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIAssistantService
{
    protected string $provider;
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        // Priority: Database settings → .env → defaults

        // Try database settings first
        $dbProvider = setting('ai_provider');
        $dbApiKey = setting('ai_api_key');
        $dbModel = setting('ai_model');

        // If database has API key, use database settings
        if (!empty($dbApiKey)) {
            $this->provider = $dbProvider ?: 'openrouter';
            $this->apiKey = $dbApiKey;
            $this->model = $dbModel ?: $this->getDefaultModel();
        } // Otherwise fall back to .env
        elseif (config('services.openrouter.api_key')) {
            $this->provider = 'openrouter';
            $this->apiKey = config('services.openrouter.api_key');
            $this->model = config('services.openrouter.model', 'openai/gpt-3.5-turbo');
        } else {
            $this->provider = config('services.ai.provider', 'openai');
            $this->apiKey = config('services.ai.api_key', '');
            $this->model = config('services.ai.model', $this->getDefaultModel());
        }
    }

    /**
     * Get default model based on provider
     */
    protected function getDefaultModel(): string
    {
        return match ($this->provider) {
            'gemini' => 'gemini-pro',
            'openai' => 'gpt-3.5-turbo',
            'openrouter' => 'openai/gpt-3.5-turbo',
            default => 'gpt-3.5-turbo',
        };
    }

    /**
     * Summarize a conversation
     */
    public function summarizeConversation(array $messages): array
    {
        $conversationText = '';
        foreach ($messages as $msg) {
            $sender = $msg['sender'] ?? 'User';
            $conversationText .= "{$sender}: {$msg['content']}\n";
        }

        $summaryPrompt = [
            [
                'role' => 'system',
                'content' => 'You are a helpful assistant that summarizes conversations. Provide a concise summary in 2-3 paragraphs focusing on main topics, key decisions, and action items.',
            ],
            [
                'role' => 'user',
                'content' => "Please summarize this conversation:\n\n{$conversationText}",
            ],
        ];

        return $this->chat($summaryPrompt);
    }

    /**
     * Send message to AI and get response
     */
    public function chat(array $messages): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'AI API key not configured. Please add your API key to the .env file.',
            ];
        }

        try {
            return match ($this->provider) {
                'gemini' => $this->chatWithGemini($messages),
                'openai' => $this->chatWithOpenAI($messages),
                'openrouter' => $this->chatWithOpenRouter($messages),
                default => $this->chatWithOpenAI($messages),
            };
        } catch (Exception $e) {
            Log::error('AI Assistant Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'External AI provider request failed.',
            ];
        }
    }

    /**
     * Chat with Google Gemini
     */
    protected function chatWithGemini(array $messages): array
    {
        // Convert OpenAI format to Gemini format
        $contents = [];
        foreach ($messages as $message) {
            $role = $message['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $message['content']]],
            ];
        }

        // Use v1 API instead of v1beta, and don't include "models/" prefix in URL
        $response = Http::timeout(30)->post(
            "https://generativelanguage.googleapis.com/v1/models/{$this->model}:generateContent?key={$this->apiKey}",
            ['contents' => $contents]
        );

        if ($response->failed()) {
            return $this->providerFailure('gemini', $response->status());
        }

        $data = $response->json();

        return [
            'success' => true,
            'message' => $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response',
            'usage' => $data['usageMetadata'] ?? null,
        ];
    }

    /**
     * Chat with OpenAI
     */
    protected function chatWithOpenAI(array $messages): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 500,
        ]);

        if ($response->failed()) {
            return $this->providerFailure('openai', $response->status());
        }

        $data = $response->json();

        return [
            'success' => true,
            'message' => $data['choices'][0]['message']['content'] ?? 'No response',
            'usage' => $data['usage'] ?? null,
        ];
    }

    /**
     * Chat with OpenRouter
     */
    protected function chatWithOpenRouter(array $messages): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => config('app.url'),
            'X-Title' => config('app.name') . ' Chat',
        ])->timeout(30)->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 500,
        ]);

        if ($response->failed()) {
            return $this->providerFailure('openrouter', $response->status());
        }

        $data = $response->json();

        return [
            'success' => true,
            'message' => $data['choices'][0]['message']['content'] ?? 'No response',
            'usage' => $data['usage'] ?? null,
        ];
    }

    /** @return array{success: false, error: string} */
    private function providerFailure(string $provider, int $status): array
    {
        Log::warning('AI provider request failed.', [
            'provider' => $provider,
            'status' => $status,
        ]);

        return [
            'success' => false,
            'error' => 'External AI provider request failed.',
        ];
    }

    /**
     * Generate reply suggestions
     */
    public function generateReplySuggestions(array $messages): array
    {
        $conversationText = '';
        foreach ($messages as $msg) {
            $sender = $msg['sender'] ?? 'User';
            $conversationText .= "{$sender}: {$msg['content']}\n";
        }

        $suggestionPrompt = [
            [
                'role' => 'system',
                'content' => 'You are a helpful assistant that suggests contextual replies. Generate exactly 3 brief, natural reply suggestions (1-2 sentences each). Return ONLY a JSON array of strings, nothing else. Example: ["Hello!", "How are you?", "Nice to meet you!"]',
            ],
            [
                'role' => 'user',
                'content' => "Based on this conversation, suggest 3 brief replies:\n\n{$conversationText}",
            ],
        ];

        $result = $this->chat($suggestionPrompt);

        if (!$result['success']) {
            return $result;
        }

        $responseText = $result['message'];
        
        // Remove markdown code blocks if present
        $responseText = preg_replace('/```json\s*/', '', $responseText);
        $responseText = preg_replace('/```\s*/', '', $responseText);
        $responseText = trim($responseText);

        // Try to parse JSON response
        $suggestions = json_decode($responseText, true);

        if (!is_array($suggestions)) {
            // If not JSON, split by newlines and clean up
            $lines = array_filter(explode("\n", $responseText));
            $suggestions = [];
            foreach ($lines as $line) {
                $line = trim($line);
                // Remove numbering, quotes, brackets
                $line = preg_replace('/^\d+[\.\)]\s*/', '', $line);
                $line = preg_replace('/^[\[\{"\']|[\]\}"\']$/', '', $line);
                $line = trim($line, ',"');
                if (!empty($line)) {
                    $suggestions[] = $line;
                }
            }
        }

        // Clean up each suggestion
        $cleanSuggestions = [];
        foreach (array_slice($suggestions, 0, 3) as $suggestion) {
            if (is_string($suggestion)) {
                $clean = trim($suggestion, ',"[]{}');
                if (!empty($clean)) {
                    $cleanSuggestions[] = $clean;
                }
            }
        }

        return [
            'success' => true,
            'suggestions' => array_values($cleanSuggestions),
        ];
    }
}
