<?php

namespace Inpi\Data;

readonly class Brand
{
    public function __construct(
        public string|null $name,
        public string $presentation,
        public string $nature,
    ) {}
}