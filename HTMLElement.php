<?php

class HTMLElement extends DOMElement {

    var $DOMDocument, $DOMXPath;

    function __construct($html) {
        $this->DOMDocument = $this->document($html);
        $this->DOMXPath = new DOMXPath($this->DOMDocument);
        return $this;
    }

    static function new($html) {
        return new static($html);
    }

    function document($html) {
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $document = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(TRUE);
        $document->loadHTML($html, LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $htmlNode = $document->getElementsByTagName('html')->item(0);
        $bodyNode = $document->getElementsByTagName('body')->item(0);
        $document->removeChild($htmlNode);
        $document->appendChild($bodyNode);
        return $document;
    }

    function generator($expression, $contextnode = NULL, $nodetype = XML_ELEMENT_NODE) {
        $nodeList = $this->DOMXPath->query($expression, $contextnode);
        if ($nodeList->length) {
            foreach ($nodeList as $i => $node) {
                if ($nodeList->item($i)->nodeType == $nodetype) {
                    yield $node;
                }
            }
        }
    }

    function elements($expression, $contextnode = NULL) {
        $elements = [];
        $generator = $this->generator($expression, $contextnode);
        foreach ($generator as $node) {
            $elements []= $node;
        }
        return $elements;
    }

    function outerHTML($element) {
        return $this->DOMDocument->saveHTML($element);
    }

    function innerHTML($element) {
        return join(array_map([$this, 'outerHTML'], iterator_to_array($element->childNodes)));
    }

    function xpath($expression, $contextnode = NULL) {
        $elements = $this->elements($expression, $contextnode);
        return array_map(function($e) {return $this->outerHTML($e);}, $elements);
    }
}
