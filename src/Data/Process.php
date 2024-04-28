<?php

namespace Inpi\Data;

readonly class Process
{
    public function __construct(
        public string $number,
        public string|null $depositDate,
        public array  $dispatches,
        public array  $holders,
        public Brand|null  $brand,
        public string|null $status,
    ) {}
}