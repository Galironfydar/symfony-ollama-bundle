# OllamaBundle

A Symfony bundle for integrating Ollama AI models into your application.

## Installation

1. Add the repository to your composer.json:
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Galironfydar/ollama-bundle"
        }
    ]
}
```

2. Install the bundle:
```bash
composer require galironfydar/ollama-bundle:dev-main
```

## Configuration

Enable the bundle in your `config/bundles.php`:

```php
return [
    // ...
    Galironfydar\OllamaBundle\OllamaBundle::class => ['all' => true],
];
```

Configure the bundle in `config/packages/ollama.yaml`:

```yaml
ollama:
    base_url: 'http://localhost:11434' # Your Ollama instance URL
```

## Usage

### Using the OllamaService

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
    }
}
```

### Using the Controller Endpoints

The bundle provides several REST endpoints:

- `GET /api/ollama/completion` - Generate text completion
- `GET /api/ollama/chat` - Chat with the model
- `GET /api/ollama/models` - List available models
- `POST /api/ollama/models/copy` - Copy a model
- `POST /api/ollama/models/{model}/pull` - Pull a model
- `GET /api/ollama/models/{model}` - Get model info
- `DELETE /api/ollama/models/{model}` - Delete a model

## License

This bundle is available under the MIT license. 