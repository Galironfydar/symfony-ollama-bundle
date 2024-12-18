<?php
namespace Galironfydar\OllamaBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class OllamaBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
