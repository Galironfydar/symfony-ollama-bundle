<?php

namespace Galironfydar\OllamaBundle\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Symfony\Component\HttpFoundation\Response;

class OllamaService
{
    private string $baseUrl;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        string $baseUrl = 'http://localhost:11434'
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>|ResponseStreamInterface
     */
    public function completion(
        string $model,
        string $prompt,
        array $options = [],
        bool $stream = false
    ): array|ResponseStreamInterface {
        $payload = array_merge([
            'model' => $model,
            'prompt' => $prompt,
            'stream' => $stream,
            'options' => [
                'temperature' => 0.7,
                'top_p' => 0.9,
                'top_k' => 40,
            ],
        ], $options);

        $response = $this->httpClient->request('POST', "{$this->baseUrl}/api/generate", [
            'json' => $payload,
        ]);

        return $stream ? $this->httpClient->stream($response) : $response->toArray();
    }

    /**
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed> $options
     * @return array<string, mixed>|ResponseStreamInterface
     */
    public function chat(
        string $model,
        array $messages,
        array $options = [],
        bool $stream = false
    ): array|ResponseStreamInterface {
        $payload = array_merge([
            'model' => $model,
            'messages' => $messages,
            'stream' => $stream,
            'options' => [
                'temperature' => 0.7,
                'top_p' => 0.9,
                'top_k' => 40,
            ],
        ], $options);

        $response = $this->httpClient->request('POST', "{$this->baseUrl}/api/chat", [
            'json' => $payload,
        ]);

        return $stream ? $this->httpClient->stream($response) : $response->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function listModels(): array
    {
        $response = $this->httpClient->request('GET', "{$this->baseUrl}/api/tags");
        return $response->toArray();
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>|ResponseStreamInterface
     */
    public function pullModel(
        string $model,
        array $options = [],
        bool $stream = false
    ): array|ResponseStreamInterface {
        $payload = array_merge([
            'name' => $model,
            'stream' => $stream,
        ], $options);

        $response = $this->httpClient->request('POST', "{$this->baseUrl}/api/pull", [
            'json' => $payload,
            'timeout' => 3600,
        ]);

        return $stream ? $this->httpClient->stream($response) : $response->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function deleteModel(string $model): array
    {
        $response = $this->httpClient->request('DELETE', "{$this->baseUrl}/api/delete", [
            'json' => [
                'name' => $model,
            ],
        ]);

        return $response->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function showModelInfo(string $model): array
    {
        $response = $this->httpClient->request('POST', "{$this->baseUrl}/api/show", [
            'json' => [
                'name' => $model,
            ],
        ]);

        return $response->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function copyModel(string $source, string $destination): array
    {
        $response = $this->httpClient->request('POST', "{$this->baseUrl}/api/copy", [
            'json' => [
                'source' => $source,
                'destination' => $destination,
            ],
        ]);

        return $response->toArray();
    }
} 