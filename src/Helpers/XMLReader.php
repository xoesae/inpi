<?php

namespace Inpi\Helpers;

use DOMDocument;

class XMLReader
{
    private \XMLReader $reader;
    private DOMDocument $doc;

    public function __construct(string $path)
    {
        $this->doc = new DOMDocument();
        $this->reader = new \XMLReader();
        $this->reader->open($path);
    }

    public function close(): void
    {
        $this->reader->close();
    }

    public function read(): false|\XMLReader
    {
        if (! $this->reader->read()) {
            return false;
        }

        return $this->reader;
    }

    public function getNode(): Node
    {
        return new Node(simplexml_import_dom($this->doc->importNode($this->reader->expand(), true)));
    }

    public function is(string $name): bool
    {
        return $this->reader->name == $name;
    }

    public function isElement(string|null $element = null): bool
    {
        $isElement = $this->reader->nodeType == \XMLReader::ELEMENT;

        if (is_null($element)) {
            return $isElement;
        }

        return $isElement && $this->is($element);
    }

    public function isEndElement(string|null $element = null): bool
    {
        $isEndElement = $this->reader->nodeType == \XMLReader::END_ELEMENT;

        if (is_null($element)) {
            return $isEndElement;
        }

        return $isEndElement && $this->is($element);
    }

    public function name(): string
    {
        return $this->reader->name;
    }
}