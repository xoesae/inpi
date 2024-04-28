<?php

namespace Inpi\Helpers;

readonly class Node
{
    public function __construct(private \SimpleXMLElement $element) {}

    public function get(string $name): string
    {
        return (string) $this->element->attributes()[$name];
    }

    public function xpath(string $path): array
    {
        $elements = $this->element->xpath($path);
        $nodes = [];

        foreach ($elements as $element) {
            $nodes[] = new self($element);
        }

        return $nodes;
    }

    public function has(string $path): bool
    {
        $elements = $this->element->xpath($path);

        return count($elements) > 0;
    }

    public function first(string $path): self
    {
        $elements = $this->element->xpath($path);

        return new self($elements[0]);
    }

    public function value(): string
    {
        return (string) $this->element;
    }
}