<?php

namespace Galironfydar\OllamaBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Galironfydar\OllamaBundle\Service\OllamaService;

class OllamaServiceTest extends TestCase
{
    private const BASE_URL = 'http://localhost:11434';
    private MockHttpClient $httpClient;
    private OllamaService $ollamaService;

    protected function setUp(): void
    {
        $this->httpClient = new MockHttpClient(function ($method, $url, $options) {
            $this->lastRequest = [
                'method' => $method,
                'url' => $url,
                'options' => $options
            ];
            return new MockResponse('[]');
        });
        $this->ollamaService = new OllamaService($this->httpClient, self::BASE_URL);
    }

    public function testCompletion(): void
    {
        $model = 'llama2';
        $prompt = 'Tell me a story';
        $expectedResponse = ['response' => 'Once upon a time...'];

        $this->httpClient = new MockHttpClient(function ($method, $url, $options) use ($expectedResponse) {
            self::assertSame('POST', $method);
            self::assertSame(self::BASE_URL . '/api/generate', $url);
            
            $requestContent = json_decode($options['body'], true);
            self::assertSame('llama2', $requestContent['model']);
            self::assertSame('Tell me a story', $requestContent['prompt']);
            self::assertFalse($requestContent['stream']);

            return new MockResponse(json_encode($expectedResponse), [
                'http_code' => 200,
                'response_headers' => ['Content-Type: application/json'],
            ]);
        });

        $this->ollamaService = new OllamaService($this->httpClient, self::BASE_URL);
        $result = $this->ollamaService->completion($model, $prompt);
        self::assertSame($expectedResponse, $result);
    }

    public function testCompletionWithStreaming(): void
    {
        $model = 'llama2';
        $prompt = 'Tell me a story';

        $this->httpClient = new MockHttpClient(function ($method, $url, $options) {
            self::assertSame('POST', $method);
            self::assertSame(self::BASE_URL . '/api/generate', $url);
            
            $requestContent = json_decode($options['body'], true);
            self::assertSame('llama2', $requestContent['model']);
            self::assertSame('Tell me a story', $requestContent['prompt']);
            self::assertTrue($requestContent['stream']);

            return new MockResponse('chunk1chunk2', [
                'http_code' => 200,
                'response_headers' => ['Content-Type: text/event-stream'],
            ]);
        });

        $this->ollamaService = new OllamaService($this->httpClient, self::BASE_URL);
        $result = $this->ollamaService->completion($model, $prompt, [], true);
        self::assertIsIterable($result);
    }

    public function testChat(): void
    {
        $model = 'llama2';
        $messages = [['role' => 'user', 'content' => 'Hello!']];
        $expectedResponse = ['response' => 'Hi there!'];

        $this->httpClient = new MockHttpClient(function ($method, $url, $options) use ($messages, $expectedResponse) {
            self::assertSame('POST', $method);
            self::assertSame(self::BASE_URL . '/api/chat', $url);
            
            $requestContent = json_decode($options['body'], true);
            self::assertSame('llama2', $requestContent['model']);
            self::assertSame($messages, $requestContent['messages']);
            self::assertFalse($requestContent['stream']);

            return new MockResponse(json_encode($expectedResponse), [
                'http_code' => 200,
                'response_headers' => ['Content-Type: application/json'],
            ]);
        });

        $this->ollamaService = new OllamaService($this->httpClient, self::BASE_URL);
        $result = $this->ollamaService->chat($model, $messages);
        self::assertSame($expectedResponse, $result);
    }

    public function testListModels(): void
    {
        $expectedResponse = ['models' => ['llama2', 'gpt4']];

        $this->httpClient = new MockHttpClient(function ($method, $url) use ($expectedResponse) {
            self::assertSame('GET', $method);
            self::assertSame(self::BASE_URL . '/api/tags', $url);

            return new MockResponse(json_encode($expectedResponse), [
                'http_code' => 200,
                'response_headers' => ['Content-Type: application/json'],
            ]);
        });

        $this->ollamaService = new OllamaService($this->httpClient, self::BASE_URL);
        $result = $this->ollamaService->listModels();
        self::assertSame($expectedResponse, $result);
    }

    public function testPullModel(): void
    {
        $model = 'llama2';
        $expectedResponse = ['status' => 'success'];

        $this->httpClient = new MockHttpClient(function ($method, $url, $options) use ($expectedResponse) {
            self::assertSame('POST', $method);
            self::assertSame(self::BASE_URL . '/api/pull', $url);
            
            $requestContent = json_decode($options['body'], true);
            self::assertSame('llama2', $requestContent['name']);
            self::assertFalse($requestContent['stream']);

            return new MockResponse(json_encode($expectedResponse), [
                'http_code' => 200,
                'response_headers' => ['Content-Type: application/json'],
            ]);
        });

        $this->ollamaService = new OllamaService($this->httpClient, self::BASE_URL);
        $result = $this->ollamaService->pullModel($model);
        self::assertSame($expectedResponse, $result);
    }

    public function testDeleteModel(): void
    {
        $model = 'llama2';
        $expectedResponse = ['status' => 'success'];

        $this->httpClient = new MockHttpClient(function ($method, $url, $options) use ($expectedResponse) {
            self::assertSame('DELETE', $method);
            self::assertSame(self::BASE_URL . '/api/delete', $url);
            
            $requestContent = json_decode($options['body'], true);
            self::assertSame('llama2', $requestContent['name']);

            return new MockResponse(json_encode($expectedResponse), [
                'http_code' => 200,
                'response_headers' => ['Content-Type: application/json'],
            ]);
        });

        $this->ollamaService = new OllamaService($this->httpClient, self::BASE_URL);
        $result = $this->ollamaService->deleteModel($model);
        self::assertSame($expectedResponse, $result);
    }

    public function testShowModelInfo(): void
    {
        $model = 'llama2';
        $expectedResponse = ['model' => 'llama2', 'parameters' => []];

        $this->httpClient = new MockHttpClient(function ($method, $url, $options) use ($expectedResponse) {
            self::assertSame('POST', $method);
            self::assertSame(self::BASE_URL . '/api/show', $url);
            
            $requestContent = json_decode($options['body'], true);
            self::assertSame('llama2', $requestContent['name']);

            return new MockResponse(json_encode($expectedResponse), [
                'http_code' => 200,
                'response_headers' => ['Content-Type: application/json'],
            ]);
        });

        $this->ollamaService = new OllamaService($this->httpClient, self::BASE_URL);
        $result = $this->ollamaService->showModelInfo($model);
        self::assertSame($expectedResponse, $result);
    }

    public function testCopyModel(): void
    {
        $source = 'llama2';
        $destination = 'my-llama2';
        $expectedResponse = ['status' => 'success'];

        $this->httpClient = new MockHttpClient(function ($method, $url, $options) use ($expectedResponse) {
            self::assertSame('POST', $method);
            self::assertSame(self::BASE_URL . '/api/copy', $url);
            
            $requestContent = json_decode($options['body'], true);
            self::assertSame('llama2', $requestContent['source']);
            self::assertSame('my-llama2', $requestContent['destination']);

            return new MockResponse(json_encode($expectedResponse), [
                'http_code' => 200,
                'response_headers' => ['Content-Type: application/json'],
            ]);
        });

        $this->ollamaService = new OllamaService($this->httpClient, self::BASE_URL);
        $result = $this->ollamaService->copyModel($source, $destination);
        self::assertSame($expectedResponse, $result);
    }
} 