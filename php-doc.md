# PHP Doc

```php
/**
 * Instantiate $this with provided HTML.
 *
 * @param string $html
 * @return HTMLElement
 */
function __construct($html) {}

/**
 * Call the __construct method statically.
 *
 * @param string $html
 * @return HTMLElement
 */
static function new($html) {}

/**
 * Build an HTML's DOMDocument object encoded in UTF-8.
 *
 * @param string $html
 * @return DOMDocument
 */
function document($html) {}

/**
 * Generate DOMElement nodes
 *
 * @param string $expression
 * @param DOMNode|DOMElement $contextnode
 * @param int $nodetype
 * @return Generator
 */
function generator($expression, $contextnode = NULL, $nodetype = XML_ELEMENT_NODE) {}

/**
 * Get an array of DOMElement objects (a.k.a. nodes) from an XPath expression.
 *
 * Not a DOMNodeList collection!
 * Each array item inherits all DOMElement properties and methods.
 * @param string $expression
 * @param DOMNode|DOMElement $contextnode
 * @return array
 */
function elements($expression, $contextnode = NULL) {}

/**
 * Get outerHTML of DOMElement.
 *
 * @param DOMElement $element
 * @return string
 */
function outerHTML($element) {}

/**
 * Get innerHTML of DOMElement.
 *
 * @param DOMElement $element
 * @return string
 */
function innerHTML($element) {

/**
 * Get an array of outerHTML strings from an XPath expression.
 *
 * @param string $expression
 * @param DOMNode|DOMElement $contextnode
 * @return array
 */
function xpath($expression, $contextnode = NULL) {}
```

---
