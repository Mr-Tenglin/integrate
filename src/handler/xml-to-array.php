<?php
/**
 * GitHub Project: Convert an xml to array
 * Copy Project Code: https://github.com/vyuldashev/xml-to-array
 */

declare (strict_types = 1);

namespace tenglin\integrate\handler;

use DOMCdataSection;
use DOMDocument;
use DOMElement;
use DOMNamedNodeMap;
use DOMText;
use Exception;

class XmlToArray
{
    protected $document;

    public function __construct(string $xml = '')
    {
        $xml = trim($xml);
        $this->document = new DOMDocument();
        try {
            $this->document->loadXML($xml);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function convert(string $xml): array
    {
        $converter = new self($xml);
        return $converter->toArray();
    }

    protected function convertAttributes(DOMNamedNodeMap $nodeMap): ?array
    {
        if ($nodeMap->length === 0) {
            return null;
        }

        $result = [];
        foreach ($nodeMap as $item) {
            $result[$item->name] = $item->value;
        }
        return ['_attributes' => $result];
    }

    protected function convertDomElement(DOMElement $element)
    {
        $sameNames = [];
        $result = $this->convertAttributes($element->attributes);

        foreach ($element->childNodes as $key => $node) {
            if (array_key_exists($node->nodeName, $sameNames)) {
                $sameNames[$node->nodeName] += 1;
            } else {
                $sameNames[$node->nodeName] = 0;
            }
        }

        foreach ($element->childNodes as $key => $node) {
            if (is_null($result)) {
                $result = [];
            }
            if ($node instanceof DOMCdataSection) {
                $result['_cdata'] = $node->data;
                continue;
            }
            if ($node instanceof DOMText) {
                if (empty($result)) {
                    $result = $node->textContent;
                } else {
                    $result['_value'] = $node->textContent;
                }
                continue;
            }
            if ($node instanceof DOMElement) {
                if ($sameNames[$node->nodeName]) {
                    if (!array_key_exists($node->nodeName, $result)) {
                        $result[$node->nodeName] = [];
                    }
                    $result[$node->nodeName][$key] = $this->convertDomElement($node);
                } else {
                    $result[$node->nodeName] = $this->convertDomElement($node);
                }
                continue;
            }
        }
        return $result;
    }

    public function toArray(): array
    {
        $result = [];
        if ($this->document->hasChildNodes()) {
            $children = $this->document->childNodes;
            foreach ($children as $child) {
                $result[$child->nodeName] = $this->convertDomElement($child);
            }
        }
        return $result;
    }
}
