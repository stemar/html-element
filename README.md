# HTMLElement.php

A PHP library to use XPath expressions to get nodes of a DOMElement node set.

The `HTMLElement` class inherits the methods and properties of the `DOMElement` class.

* Will convert HTML entities to UTF-8 characters.
* Will not repair HTML.
* Will not format HTML.
* Will remove invalid tags.

- [The DOMElement class](https://www.php.net/manual/en/class.domelement.php)
- [DOMDocument outerHTML](https://stackoverflow.com/a/21382265)
- [DOMNode innerHTML](https://stackoverflow.com/a/39193507)
- [LIBXML_HTML_NOIMPLIED root node](https://stackoverflow.com/a/36547335)
- [What is the difference between DOMXPath::evaluate and DOMXPath::query?](https://stackoverflow.com/q/23793816)

## Goal

Reduce the repetitive lines of code needed to query a `DOMElement` with an XPath expression.


> `DOMDocument::loadHTML()` will add `<body>` tag as the root node
> but this doesn't affect XPath expressions.

## Installation

```bash
git clone --depth 1 https://github.com/stemar/html-element.git
cd html-element
```

---

## Test

### One local file

```bash
wget -O phpunit https://phar.phpunit.de/phpunit-8.phar
php phpunit HTMLElementTest.php
```

### With Composer

```bash
curl -sS https://getcomposer.org/installer -o composer | php
composer require phpunit/phpunit
vendor/bin/phpunit HTMLElementTest.php
```

---

## Usage

Require this library in your code.

```php
require_once __DIR__.'/HTMLElement.php';
$html = "<p>Example</p>";
$html_element = new HTMLElement($html);
$elements = $html_element->elements('//p');
```

Or add a `namespace` at the top of `HTMLElement.php` and `use` it in your code with autoload.

## Regular way

1. Convert HTML to `UTF-8`.
2. Create a `DOMDocument` instance with the HTML.
3. Make a HTML object from the `DOMDocument` instance.
4. Create a `DOMXPath` instance with the HTML `DOMDocument` object.
5. Query the `DOMXPath` instance with a XPath expression to get a `DOMNodeList` collection.
6. Iterate the `DOMNodeList` collection to each `DOMNode` object.
7. Check if each `DOMNode` object is a `DOMElement` object.
8. Get the `nodeValue` of each `DOMElement` object to get the content in the node.

> No `innerHTML` property is available to a `DOMElement` object.

### Parsing the DOMElement nodes

Try in a `php -a` console:

```php
$html = '</z><p>Ø&Uuml; <a href="#">Link</a></p><p>Second para</p>';
$doc = new \DOMDocument();
libxml_use_internal_errors(TRUE);
$doc->loadHTML($html, LIBXML_HTML_NODEFDTD);
libxml_clear_errors();
$xpath = new \DOMXPath($doc);
$nodeList = $xpath->query('//p');
if ($nodeList->length) {
    foreach ($nodeList as $i => $node) {
        if ($nodeList->item($i)->nodeType == XML_ELEMENT_NODE) {
            echo $node->nodeValue, PHP_EOL;
            echo $node->ownerDocument->saveHTML($node), PHP_EOL; // like outerHTML
        }
    }
}
```

Result:

```console
ÃÜ Link
<p>ÃÜ <a href="#">Link</a></p>
Second para
<p>Second para</p>
```

### Observations

- Invalid tag `</z>` is correctly removed.
- The UTF-8 character Ø gets incorrectly double-encoded to `Ã<0x98>`.
- The HTML entity `&Uuml;` gets correctly encoded to Ü.
- The loop through the NodeList collection has to be performed every time you query with a XPath expression.
- The flow of classes you have to code through to get a DOMElement is:
    - DOMDocument => DOMXPath => DOMNodeList => DOMNode => DOMElement

## New way

1. Instantiate a `HTMLElement` from HTML.
2. Get `DOMElement` nodes from this instance.
3. Get the `nodeValue` of each `DOMElement` object to get the content in the node.

### Parsing the HTMLElement nodes

Try in a `php -a` console:

```php
require 'HTMLElement.php';
$html = '</z><p>Ø&Uuml; <a href="#">Link</a></p><p>Second para</p>';
$html_element = new HTMLElement($html);
$elements = $html_element->elements('//p');
foreach ($elements as $element) {
    echo $element->nodeValue, PHP_EOL;
    echo $html_element->outerHTML($element), PHP_EOL;
    echo $html_element->innerHTML($element), PHP_EOL;
}
var_export(HTMLElement::new($html)->xpath('//p'));
```

Result:

```console
ØÜ Link
<p>ØÜ <a href="#">Link</a></p>
ØÜ <a href="#">Link</a>
Second para
<p>Second para</p>
Second para
array (
  0 => '<p>ØÜ <a href="#">Link</a></p>',
  1 => '<p>Second para</p>',
)
```

### Parsing child nodes

You can get the `<a>` child nodes of the `<p>` node by using the second argument (`$contextnode`) in `HTMLElement::nodes()`.

Try in a `php -a` console:

```php
require 'HTMLElement.php';
$html = '</z><p>Ø&Uuml; <a href="#">Link</a></p><p>Second para</p>';
$html_element = new HTMLElement($html);
// Get the first <p> node
$contextnode = $html_element->elements('//p')[0];
// Get the <a> nodes only under this <p> context node
$elements = $html_element->elements('//a', $contextnode);
foreach ($elements as $element) {
    echo $element->nodeValue, PHP_EOL;
    echo $element->getAttribute('href'), PHP_EOL;
}
```

Result:

```console
Link
#
```

---

## PHP Doc

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
