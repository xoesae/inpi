<?php

namespace Inpi\Data;

readonly class Dispatch
{
    public function __construct(
        public string $code,
        public string $name,
    ) {}
}