<?php

namespace Galironfydar\OllamaBundle\Controller;

use Galironfydar\OllamaBundle\Service\OllamaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\ChunkInterface;

#[Route('/api/ollama')]
class OllamaController extends AbstractController
{
    public function __construct(
        private readonly OllamaService $ollamaService
    ) {
    }

    #[Route('/completion', name: 'ollama_completion', methods: ['GET'])]
    public function completion(Request $request): Response
    {
        $model = $request->query->get('model', 'llama2');
        $prompt = $request->query->get('prompt', 'Tell me a story');
        $stream = filter_var($request->query->get('stream', 'true'), FILTER_VALIDATE_BOOLEAN);
        
        $options = [
            'options' => [
                'temperature' => (float) $request->query->get('temperature', 0.7),
                'top_p' => (float) $request->query->get('top_p', 0.9),
                'top_k' => (int) $request->query->get('top_k', 40),
                'seed' => $request->query->get('seed') ? (int) $request->query->get('seed') : null,
            ],
        ];

        if (!$stream) {
            $response = $this->ollamaService->completion($model, $prompt, $options, false);
            return new JsonResponse($response);
        }

        $response = new StreamedResponse(function () use ($model, $prompt, $options) {
            $stream = $this->ollamaService->completion($model, $prompt, $options, true);
            
            foreach ($stream as $chunk) {
                if ($chunk instanceof ChunkInterface) {
                    echo $chunk->getContent() . "\n";
                }
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    #[Route('/chat', name: 'ollama_chat', methods: ['GET'])]
    public function chat(Request $request): Response
    {
        $model = $request->query->get('model', 'llama2');
        $message = $request->query->get('message', 'Tell me about yourself');
        $stream = filter_var($request->query->get('stream', 'true'), FILTER_VALIDATE_BOOLEAN);
        
        $options = [
            'options' => [
                'temperature' => (float) $request->query->get('temperature', 0.7),
                'top_p' => (float) $request->query->get('top_p', 0.9),
                'top_k' => (int) $request->query->get('top_k', 40),
                'seed' => $request->query->get('seed') ? (int) $request->query->get('seed') : null,
            ],
        ];

        $messages = [
            ['role' => 'user', 'content' => $message]
        ];

        if (!$stream) {
            $response = $this->ollamaService->chat($model, $messages, $options, false);
            return new JsonResponse($response);
        }

        $response = new StreamedResponse(function () use ($model, $messages, $options) {
            $stream = $this->ollamaService->chat($model, $messages, $options, true);
            
            foreach ($stream as $chunk) {
                if ($chunk instanceof ChunkInterface) {
                    echo $chunk->getContent() . "\n\n";
                }
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    #[Route('/models', name: 'ollama_list_models', methods: ['GET'])]
    public function listModels(): JsonResponse
    {
        return new JsonResponse($this->ollamaService->listModels());
    }

    #[Route('/models/copy', name: 'ollama_copy_model', methods: ['POST'])]
    public function copyModel(Request $request): JsonResponse
    {
        $source = $request->query->get('source');
        $destination = $request->query->get('destination');

        if (!$source || !$destination) {
            return new JsonResponse(['error' => 'Source and destination are required'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->ollamaService->copyModel($source, $destination));
    }

    #[Route('/models/{model}/pull', name: 'ollama_pull_model', methods: ['POST', 'GET'])]
    public function pullModel(Request $request, string $model): Response
    {
        set_time_limit(3600); // Set PHP timeout to 1 hour for large model downloads
        
        $stream = filter_var($request->query->get('stream', 'true'), FILTER_VALIDATE_BOOLEAN);
        
        if (!$stream) {
            $response = $this->ollamaService->pullModel($model, [], false);
            return new JsonResponse($response);
        }

        $response = new StreamedResponse(function () use ($model) {
            $stream = $this->ollamaService->pullModel($model, [], true);
            
            foreach ($stream as $chunk) {
                if ($chunk instanceof ChunkInterface) {
                    $data = json_decode($chunk->getContent());
                    if ($data) {
                        echo "data: " . json_encode([
                            'status' => $data->status ?? '',
                            'completed' => $data->completed ?? 0,
                            'total' => $data->total ?? 0,
                            'done' => $data->done ?? false,
                        ]) . "\n\n";
                        
                        if ($data->done ?? false) {
                            break;
                        }
                    }
                }
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    #[Route('/models/{model}', name: 'ollama_show_model', methods: ['GET'])]
    public function showModel(string $model): JsonResponse
    {
        return new JsonResponse($this->ollamaService->showModelInfo($model));
    }

    #[Route('/models/{model}', name: 'ollama_delete_model', methods: ['DELETE'])]
    public function deleteModel(string $model): JsonResponse
    {
        return new JsonResponse($this->ollamaService->deleteModel($model));
    }
} 