<?php

namespace Inpi\Data;

readonly class Holder
{
    public function __construct(
        public string $name,
        public string $country,
        public string $state,
    ) {}
}