# OllamaBundle

[![Tests](https://github.com/Galironfydar/symfony-ollama-bundle/actions/workflows/tests.yml/badge.svg)](https://github.com/Galironfydar/symfony-ollama-bundle/actions/workflows/tests.yml)

A Symfony bundle for integrating Ollama AI models into your application.

## Installation

1. Add the repository to your composer.json:
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Galironfydar/symfony-ollama-bundle.git"
        }
    ]
}
```

2. Install the bundle:
```bash
composer require galironfydar/ollama-bundle:dev-main
```

## Configuration

The bundle will automatically register itself in your Symfony application. Just add your configuration in `config/packages/ollama.yaml`:

```yaml
ollama:
    base_url: 'http://localhost:11434' # Your Ollama instance URL
```

All configuration options:
- `base_url` (default: 'http://localhost:11434'): The base URL of your Ollama instance

The bundle will automatically:
- Register the OllamaService as a service in your container
- Configure the HTTP client for API communication
- Set up all necessary parameters

## Usage

The bundle provides the `OllamaService` which you can use to interact with your Ollama instance:

```php
use Galironfydar\OllamaBundle\Service\OllamaService;

class YourService
{
    public function __construct(
        private readonly OllamaService $ollamaService
    ) {
    }

    public function example(): void
    {
        // Generate text completion
        $response = $this->ollamaService->completion('llama2', 'Tell me a story');

        // Chat with the model
        $response = $this->ollamaService->chat('llama2', [
            ['role' => 'user', 'content' => 'Hello!']
        ]);

        // List available models
        $models = $this->ollamaService->listModels();

        // Pull a model
        $response = $this->ollamaService->pullModel('llama2');

        // Delete a model
        $response = $this->ollamaService->deleteModel('llama2');

        // Get model info
        $response = $this->ollamaService->showModelInfo('llama2');

        // Copy a model
        $response = $this->ollamaService->copyModel('llama2', 'my-llama2');
    }
}
```

### Advanced Usage

Each method supports additional options and streaming responses:

```php
// Completion with options and streaming
$response = $this->ollamaService->completion(
    'llama2',
    'Tell me a story',
    [
        'options' => [
            'temperature' => 0.7,
            'top_p' => 0.9,
            'top_k' => 40,
        ]
    ],
    true // Enable streaming
);

// Process streaming response
foreach ($response as $chunk) {
    if ($chunk instanceof ChunkInterface) {
        echo $chunk->getContent();
    }
}
```

## Examples

Check out the `examples` directory for implementation examples:

- `examples/Controller/OllamaController.php`: A complete REST API implementation showing how to use the service with streaming responses and proper error handling.

## License

This bundle is available under the MIT license. 